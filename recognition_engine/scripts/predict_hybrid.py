import cv2
import numpy as np
import sys

# ── Preprocessing LBPH (harus sama persis dengan train_lbph.py) ──────────
def preprocess_lbph(gray, x, y, w, h, img_w, img_h):
    mx, my = int(w * 0.12), int(h * 0.12)
    x1 = max(0, x - mx)
    y1 = max(0, y - my)
    x2 = min(img_w, x + w + mx)
    y2 = min(img_h, y + h + my)

    face_roi = gray[y1:y2, x1:x2]
    if face_roi.size == 0:
        return None

    face_roi = cv2.resize(face_roi, (200, 200), interpolation=cv2.INTER_LANCZOS4)
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
    face_roi = clahe.apply(face_roi)
    face_roi = cv2.GaussianBlur(face_roi, (3, 3), 0)
    face_roi = cv2.equalizeHist(face_roi)
    return face_roi


# ── Preprocessing VGG16 ───────────────────────────────────────────────────
def preprocess_vgg16(frame, x, y, w, h):
    try:
        from tensorflow.keras.applications.vgg16 import preprocess_input
        from tensorflow.keras.preprocessing.image import img_to_array

        img_h, img_w = frame.shape[:2]
        mx, my = int(w * 0.18), int(h * 0.18)
        x1 = max(0, x - mx)
        y1 = max(0, y - my)
        x2 = min(img_w, x + w + mx)
        y2 = min(img_h, y + h + my)

        face_roi = frame[y1:y2, x1:x2]
        if face_roi.size == 0:
            return None

        face_resized = cv2.resize(face_roi, (224, 224), interpolation=cv2.INTER_AREA)
        face_array = img_to_array(face_resized)
        face_array = np.expand_dims(face_array, axis=0)
        return preprocess_input(face_array)
    except Exception as e:
        print(f"[VGG Preprocess Error] {e}", file=sys.stderr)
        return None


# ── Konversi LBPH distance → confidence yang lebih akurat ────────────────
def lbph_dist_to_conf(dist):
    """
    LBPH distance:
      < 50  = sangat yakin (90%+)
      50-80 = cukup yakin (75%-85%)
      > 80  = tidak yakin
    Catatan: min di-set ke 0.0 (bukan 10) agar nilai tidak pernah negatif
    saat distance sangat besar (> 333).
    """
    if dist <= 0:
        return 100.0
    # Formula linear-mapping yang ramah persentase untuk display dashboard
    conf = 100.0 - (dist * 0.3)
    return round(max(0.0, min(100.0, conf)), 1)  # min=0 agar tidak bisa negatif


# ── Fusion Logic ──────────────────────────────────────────────────────────
def predict_fusion(frame, x, y, w, h,
                   model_vgg16, le_vgg16,
                   recognizer_lbph, label_map,
                   confidence_threshold=80):
    """
    Threshold yang aman:
      VGG16  → harus > 90% untuk langsung diterima
      LBPH   → distance harus < 75 (bukan formula lama)
      Fusion → keduanya setuju → lebih yakin
    """
    img_h, img_w = frame.shape[:2]
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

    vgg_nama, vgg_id, vgg_conf   = "Unknown", None, 0.0
    lbph_nama, lbph_id, lbph_conf = "Unknown", None, 0.0
    lbph_dist_raw = 9999

    # ── 1. Prediksi VGG16 ─────────────────────────────────────────────────
    if model_vgg16 is not None and le_vgg16 is not None:
        vgg_input = preprocess_vgg16(frame, x, y, w, h)
        if vgg_input is not None:
            try:
                preds = model_vgg16.predict(vgg_input, verbose=0)
                similarity = float(np.max(preds))
                label_idx  = int(np.argmax(preds))
                raw_label  = le_vgg16.inverse_transform([label_idx])[0]

                try:
                    vgg_id   = int(raw_label.split("_")[-1])
                    vgg_nama = raw_label.rsplit('_', 1)[0].replace('_', ' ')
                except Exception:
                    vgg_id, vgg_nama = None, raw_label

                vgg_conf = similarity * 100
            except Exception as e:
                print(f"[VGG Predict Error] {e}", file=sys.stderr)

    # ── 2. Prediksi LBPH ──────────────────────────────────────────────────
    if recognizer_lbph is not None:
        lbph_input = preprocess_lbph(gray, x, y, w, h, img_w, img_h)
        if lbph_input is not None:
            try:
                id_pred, dist = recognizer_lbph.predict(lbph_input)
                lbph_dist_raw = dist
                lbph_conf     = lbph_dist_to_conf(dist)

                entry = label_map.get(id_pred) or label_map.get(str(id_pred))
                if isinstance(entry, dict):
                    lbph_nama = entry.get('nama', f"ID_{id_pred}")
                    lbph_id   = entry.get('id', id_pred)
                elif isinstance(entry, str):
                    try:
                        lbph_nama = entry.rsplit('_', 1)[0]
                        lbph_id   = int(entry.split('_')[-1])
                    except Exception:
                        lbph_nama, lbph_id = entry, id_pred
                else:
                    lbph_nama, lbph_id = f"ID_{id_pred}", id_pred
            except Exception as e:
                print(f"[LBPH Predict Error] {e}", file=sys.stderr)

    # ── 3. Fusion Decision ────────────────────────────────────────────────
    vgg_ok  = vgg_conf  >= 90.0               # VGG sangat yakin
    lbph_ok = lbph_dist_raw < 70              # LBPH sangat yakin (distance rendah)
    both_agree = (vgg_id is not None and lbph_id is not None
                  and vgg_id == lbph_id)

    # Kasus terbaik: keduanya setuju
    if vgg_ok and lbph_ok and both_agree:
        combined = (vgg_conf * 0.6) + (lbph_conf * 0.4)
        return True, vgg_nama, vgg_id, round(combined, 1), "Hybrid"

    # VGG sangat yakin sendiri
    if vgg_ok and vgg_conf >= 92.0:
        return True, vgg_nama, vgg_id, round(vgg_conf, 1), "VGG16"

    # LBPH sangat yakin sendiri (distance sangat kecil)
    # Jika VGG16 tidak aktif (di server web), gunakan toleransi aman 75
    lbph_threshold = 75 if model_vgg16 is None else 55
    if lbph_dist_raw < lbph_threshold:
        return True, lbph_nama, lbph_id, round(lbph_conf, 1), "LBPH"

    # Tidak ada yang cukup yakin
    best_conf = max(vgg_conf, lbph_conf)
    print(f"[DEBUG] VGG={vgg_conf:.1f}% dist_LBPH={lbph_dist_raw:.1f} → REJECTED", file=sys.stderr)
    return False, "Unknown", None, round(best_conf, 1), "None"
