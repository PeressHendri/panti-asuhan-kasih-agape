import cv2
import numpy as np

def preprocess_lbph(gray, x, y, w, h, img_w, img_h):
    mx, my = int(w * 0.12), int(h * 0.12)
    x1, y1 = max(0, x - mx), max(0, y - my)
    x2, y2 = min(img_w, x + w + mx), min(img_h, y + h + my)
    
    face_roi = gray[y1:y2, x1:x2]
    if face_roi.size == 0: return None
    
    face_roi = cv2.resize(face_roi, (200, 200), interpolation=cv2.INTER_LANCZOS4)
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
    face_roi = clahe.apply(face_roi)
    face_roi = cv2.GaussianBlur(face_roi, (3, 3), 0)
    face_roi = cv2.equalizeHist(face_roi)
    return face_roi

def preprocess_vgg16(frame, x, y, w, h):
    try:
        from tensorflow.keras.applications.vgg16 import preprocess_input
        from tensorflow.keras.preprocessing.image import img_to_array
        
        img_h, img_w = frame.shape[:2]
        mx, my = int(w * 0.18), int(h * 0.18)
        x1, y1 = max(0, x - mx), max(0, y - my)
        x2, y2 = min(img_w, x + w + mx), min(img_h, y + h + my)
        
        face_roi = frame[y1:y2, x1:x2]
        if face_roi.size == 0: return None
            
        face_resized = cv2.resize(face_roi, (224, 224), interpolation=cv2.INTER_AREA)
        face_array = img_to_array(face_resized)
        face_array = np.expand_dims(face_array, axis=0)
        # Menggunakan prepocess_input bawaan Keras (std mean subtraction)
        return preprocess_input(face_array)
    except Exception as e:
        print("[VGG Preprocess Error]", e)
        return None

def predict_fusion(frame, x, y, w, h, model_vgg16, le_vgg16, recognizer_lbph, label_map, confidence_threshold=75):
    """
    Hybrid Prediction: VGG16 + LBPH.
    Jika VGG16 yakin (similarity > 0.85), pakai VGG16.
    Jika tidak, cek LBPH. Jika LBPH yakin, pakai LBPH.
    Jika tidak ada yang yakin, return Unknown.
    """
    img_h, img_w = frame.shape[:2]
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    
    vgg_nama, vgg_id, vgg_conf = "Unknown", None, 0
    lbph_nama, lbph_id, lbph_conf = "Unknown", None, 0
    
    # 1. Coba VGG16
    if model_vgg16 is not None:
        vgg_input = preprocess_vgg16(frame, x, y, w, h)
        if vgg_input is not None:
            preds = model_vgg16.predict(vgg_input, verbose=0)
            similarity = float(np.max(preds))
            if similarity > 0.40: # Threshold VGG dasar
                label_idx = int(np.argmax(preds))
                raw_label = le_vgg16.inverse_transform([label_idx])[0]
                try:
                    vgg_id = int(raw_label.split("_")[-1])
                    vgg_nama = raw_label.rsplit('_', 1)[0]
                except:
                    vgg_id, vgg_nama = None, raw_label
                vgg_conf = similarity * 100

    # 2. Coba LBPH
    if recognizer_lbph is not None:
        lbph_input = preprocess_lbph(gray, x, y, w, h, img_w, img_h)
        if lbph_input is not None:
            id_pred, dist = recognizer_lbph.predict(lbph_input)
            lbph_conf_raw = max(0, 100 - (dist / 1.5))
            if dist < 90: # Jika LBPH cukup yakin
                entry = label_map.get(id_pred) or label_map.get(str(id_pred))
                if isinstance(entry, dict):
                    lbph_nama, lbph_id = entry.get('nama', f"ID_{id_pred}"), entry.get('id', id_pred)
                elif isinstance(entry, str):
                    try:
                        lbph_nama, lbph_id = entry.rsplit('_', 1)[0], int(entry.split('_')[-1])
                    except:
                        lbph_nama, lbph_id = entry, id_pred
                else:
                    lbph_nama, lbph_id = f"ID_{id_pred}", id_pred
                lbph_conf = lbph_conf_raw

    # 3. Fusion Logic (Decision)
    if vgg_conf > 85.0:
        return True, vgg_nama, vgg_id, vgg_conf, "VGG16"
    
    if lbph_conf > confidence_threshold:
        return True, lbph_nama, lbph_id, lbph_conf, "LBPH"
        
    if vgg_conf > 60.0:
        return True, vgg_nama, vgg_id, vgg_conf, "VGG16 (Low Conf)"

    return False, "Unknown", None, max(vgg_conf, lbph_conf), "None"
