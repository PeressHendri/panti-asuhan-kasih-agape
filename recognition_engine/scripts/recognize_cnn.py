import os
import sys
import json
import logging
import pickle

# Perbaikan untuk VPS CloudPanel: Pastikan Python membaca package dari instalasi user lokal secara dinamis
import glob
homes = ["/home/pantiasuhankasihagape", os.path.expanduser('~')]
for home in set(homes):
    if os.path.exists(home):
        pattern = os.path.join(home, '.local/lib/python3.*/site-packages')
        for site_path in glob.glob(pattern):
            if os.path.exists(site_path) and site_path not in sys.path:
                sys.path.insert(0, site_path)

os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3' 

try:
    import cv2
    import numpy as np
    import tensorflow as tf
    from tensorflow.keras.models import load_model
    from tensorflow.keras.preprocessing.image import img_to_array
except ImportError as e:
    print(json.dumps({"success": False, "message": f"Library Error: {str(e)} (Pastikan install tensorflow dan opencv di VPS)"}))
    sys.exit(1)

# Matikan log Tensorflow
tf.get_logger().setLevel('ERROR')

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODELS_DIR = os.path.join(BASE_DIR, "models", "vgg16")

# Pilih model VGG16 terbaik
VGG16_MODEL_PATH = os.path.join(MODELS_DIR, "best_adam.h5")
if not os.path.exists(VGG16_MODEL_PATH):
    VGG16_MODEL_PATH = os.path.join(MODELS_DIR, "model_vgg16_adam.h5")

ENCODER_PATH = os.path.join(MODELS_DIR, "label_encoder.pkl")
VGG16_SIM_THRESHOLD = 0.40  # Threshold standar 40%

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "message": "Parameter path gambar tidak diberikan"}))
        return

    img_path = sys.argv[1]
    if not os.path.exists(img_path):
        print(json.dumps({"success": False, "message": f"File gambar tidak ditemukan: {img_path}"}))
        return

    if not os.path.exists(VGG16_MODEL_PATH) or not os.path.exists(ENCODER_PATH):
        print(json.dumps({"success": False, "message": "Model VGG16 hasil training (.h5) atau Label Encoder (.pkl) tidak ditemukan di VPS."}))
        return

    try:
        # Buat wrapper layer untuk mengabaikan 'quantization_config' (Kompatibilitas TF baru ke TF lama di VPS)
        from tensorflow.keras.layers import Dense, Conv2D, MaxPooling2D, Flatten, Dropout, GlobalAveragePooling2D, BatchNormalization
        
        def pop_quant(kwargs):
            kwargs.pop('quantization_config', None)
            return kwargs
            
        class PDense(Dense):
            def __init__(self, **kwargs): super().__init__(**pop_quant(kwargs))
        class PConv2D(Conv2D):
            def __init__(self, **kwargs): super().__init__(**pop_quant(kwargs))
        class PMaxPooling2D(MaxPooling2D):
            def __init__(self, **kwargs): super().__init__(**pop_quant(kwargs))
        class PFlatten(Flatten):
            def __init__(self, **kwargs): super().__init__(**pop_quant(kwargs))
        class PDropout(Dropout):
            def __init__(self, **kwargs): super().__init__(**pop_quant(kwargs))
        class PGlobalAveragePooling2D(GlobalAveragePooling2D):
            def __init__(self, **kwargs): super().__init__(**pop_quant(kwargs))
        class PBatchNormalization(BatchNormalization):
            def __init__(self, **kwargs): super().__init__(**pop_quant(kwargs))

        custom_objs = {
            'Dense': PDense,
            'Conv2D': PConv2D,
            'MaxPooling2D': PMaxPooling2D,
            'Flatten': PFlatten,
            'Dropout': PDropout,
            'GlobalAveragePooling2D': PGlobalAveragePooling2D,
            'BatchNormalization': PBatchNormalization
        }
        
        # Muat Model VGG16 dan Encoder dengan custom wrapper
        model = load_model(VGG16_MODEL_PATH, custom_objects=custom_objs, compile=False)
        with open(ENCODER_PATH, 'rb') as f:
            le = pickle.load(f)
    except Exception as e:
        print(json.dumps({"success": False, "message": f"Gagal memuat model VGG16: {str(e)}"}))
        return

    # Baca Gambar
    img = cv2.imread(img_path)
    if img is None:
        print(json.dumps({"success": False, "message": "Gagal membaca file gambar dari storage."}))
        return

    # Deteksi Wajah
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    cascade_default = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
    cascade_alt = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_alt2.xml')

    faces = cascade_default.detectMultiScale(gray, scaleFactor=1.05, minNeighbors=4, minSize=(50, 50))
    if len(faces) == 0:
        faces = cascade_alt.detectMultiScale(gray, scaleFactor=1.05, minNeighbors=3, minSize=(50, 50))

    if len(faces) == 0:
        print(json.dumps({"success": False, "message": "Wajah tidak terdeteksi oleh kamera. Posisikan wajah lebih jelas."}))
        return

    # Ambil wajah terbesar
    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    x, y, w, h = faces[0]

    try:
        # Preprocessing Wajah dengan Margin 15% untuk VGG16
        img_h, img_w = img.shape[:2]
        margin_x = int(w * 0.15)
        margin_y = int(h * 0.15)
        
        x1 = max(0, x - margin_x)
        y1 = max(0, y - margin_y)
        x2 = min(img_w, x + w + margin_x)
        y2 = min(img_h, y + h + margin_y)
        
        face_roi = img[y1:y2, x1:x2]
        face_resized = cv2.resize(face_roi, (224, 224))
        
        face_rgb = cv2.cvtColor(face_resized, cv2.COLOR_BGR2RGB)
        face_array = img_to_array(face_rgb) / 255.0
        face_array = np.expand_dims(face_array, axis=0)
        
        # 1. Prediksi dengan VGG16
        preds = model.predict(face_array, verbose=0)
        vgg16_similarity = float(np.max(preds))
        vgg16_label_idx = int(np.argmax(preds))
        raw_label = le.inverse_transform([vgg16_label_idx])[0]

        try:
            vgg16_child_id = int(raw_label.split("_")[-1])
            vgg16_nama = raw_label.rsplit('_', 1)[0].replace('_', ' ')
        except Exception:
            vgg16_child_id = None
            vgg16_nama = raw_label
            
        vgg16_confidence = round(vgg16_similarity * 100, 1)

        # 2. Prediksi dengan LBPH (Ensemble Learning)
        lbph_child_id = None
        lbph_nama = None
        lbph_confidence = 0.0
        
        LBPH_MODEL_PATH = os.path.join(BASE_DIR, "models", "lbph", "trainer.yml")
        LBPH_MAP_PATH = os.path.join(BASE_DIR, "models", "lbph", "label_map.pkl")
        
        if os.path.exists(LBPH_MODEL_PATH) and os.path.exists(LBPH_MAP_PATH):
            try:
                lbph_recognizer = cv2.face.LBPHFaceRecognizer_create()
                lbph_recognizer.read(LBPH_MODEL_PATH)
                with open(LBPH_MAP_PATH, "rb") as f:
                    lbph_map = pickle.load(f)
                
                # Preprocess LBPH (CLAHE + crop)
                l_margin_x = int(w * 0.10)
                l_margin_y = int(h * 0.10)
                lx1 = max(0, x - l_margin_x)
                ly1 = max(0, y - l_margin_y)
                lx2 = min(img_w, x + w + l_margin_x)
                ly2 = min(img_h, y + h + l_margin_y)
                
                gray_face = gray[ly1:ly2, lx1:lx2]
                gray_face = cv2.resize(gray_face, (200, 200), interpolation=cv2.INTER_LANCZOS4)
                clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
                gray_face = clahe.apply(gray_face)
                gray_face = cv2.GaussianBlur(gray_face, (3, 3), 0)
                
                id_pred, conf = lbph_recognizer.predict(gray_face)
                
                # Konversi Distance LBPH ke Persentase (Distance 0 = 100%, 100 = 0%)
                lbph_confidence = round(max(0.0, 100.0 - conf), 1)
                
                entry = lbph_map.get(id_pred) or lbph_map.get(str(id_pred))
                if entry:
                    if isinstance(entry, dict):
                        lbph_child_id = entry.get('id', id_pred)
                        lbph_nama = entry.get('nama', f"ID_{id_pred}")
                    elif isinstance(entry, str):
                        parts = entry.rsplit('_', 1)
                        lbph_child_id = int(parts[-1]) if len(parts) > 1 and parts[-1].isdigit() else id_pred
                        lbph_nama = parts[0] if len(parts) > 1 else entry
                    lbph_nama = lbph_nama.replace('_', ' ').strip()
            except Exception as e:
                pass # Abaikan jika LBPH gagal, tetap lanjut pakai VGG16

        # 3. ENSEMBLE DECISION (Pemilihan Pemenang)
        # Jika kedua model berhasil mendeteksi, bandingkan persentase akurasinya!
        if lbph_confidence > vgg16_confidence and lbph_child_id is not None:
            final_id = lbph_child_id
            final_nama = lbph_nama
            final_conf = lbph_confidence
            final_model = "LBPH (Ensemble Winner)"
        else:
            final_id = vgg16_child_id
            final_nama = vgg16_nama
            final_conf = vgg16_confidence
            final_model = "VGG16 (Ensemble Winner)"

        # Threshold gabungan minimum
        if final_conf < 20.0:
            print(json.dumps({
                "success": False,
                "message": f"Wajah terdeteksi tapi kecocokan terlalu rendah ({final_conf}%).",
                "confidence": final_conf
            }))
            return

        print(json.dumps({
            "success": True,
            "child_id": final_id,
            "nama": final_nama,
            "confidence": final_conf,
            "distance": round(1.0 - (final_conf/100.0), 3),
            "model": final_model
        }))

    except Exception as e:
        print(json.dumps({"success": False, "message": f"Error selama inferensi Ensemble: {str(e)}"}))

if __name__ == "__main__":
    main()
