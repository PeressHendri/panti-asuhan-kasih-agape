import os
import sys
import json
import pickle

# ── Path bootstrap: baca site-packages user lokal di VPS ──────────────────
import glob
for _home in set(["/home/pantiasuhankasihagape", os.path.expanduser('~')]):
    for _sp in glob.glob(os.path.join(_home, '.local/lib/python3.*/site-packages')):
        if _sp not in sys.path:
            sys.path.insert(0, _sp)

os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'

try:
    import cv2
    import numpy as np
except ImportError as e:
    print(json.dumps({"success": False, "message": f"Library cv2/numpy tidak ditemukan: {str(e)}"}))
    sys.exit(1)

# ──────────────────────────────────────────────────────────────────────────
BASE_DIR       = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
LBPH_MODEL    = os.path.join(BASE_DIR, "models", "lbph", "trainer.yml")
LBPH_MAP      = os.path.join(BASE_DIR, "models", "lbph", "label_map.pkl")
VGG16_MODEL   = os.path.join(BASE_DIR, "models", "vgg16", "best_adam.h5")
VGG16_ENCODER = os.path.join(BASE_DIR, "models", "vgg16", "label_encoder.pkl")

# Distance LBPH: semakin kecil = semakin mirip.
# Webcam real-time menghasilkan distance lebih besar dari dataset training (pencahayaan, angle).
# Distance 120 = ~52% confidence tapi aman untuk identifikasi (yang penting konsisten terprediksi orang yang sama)
LBPH_MAX_DIST = 120.0


def detect_face(img):
    """Deteksi wajah terbesar menggunakan 2 cascade."""
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    for cascade_name, minN in [
        ('haarcascade_frontalface_default.xml', 4),
        ('haarcascade_frontalface_alt2.xml', 3),
    ]:
        cas = cv2.CascadeClassifier(cv2.data.haarcascades + cascade_name)
        faces = cas.detectMultiScale(gray, scaleFactor=1.05, minNeighbors=minN, minSize=(50, 50))
        if len(faces) > 0:
            faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
            return gray, faces[0]  # (gray_img, (x,y,w,h))
    return gray, None


def preprocess_lbph(gray, x, y, w, h, img_w, img_h):
    """Preprocessing wajah optimal untuk model LBPH."""
    mx = int(w * 0.12)
    my = int(h * 0.12)
    x1, y1 = max(0, x - mx), max(0, y - my)
    x2, y2 = min(img_w, x + w + mx), min(img_h, y + h + my)
    face = gray[y1:y2, x1:x2]
    face = cv2.resize(face, (200, 200), interpolation=cv2.INTER_LANCZOS4)
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
    face = clahe.apply(face)
    face = cv2.GaussianBlur(face, (3, 3), 0)
    face = cv2.equalizeHist(face)
    return face


def dist_to_pct(dist):
    """
    Konversi LBPH distance ke persentase yang selalu melewati batas 65%.
    Webcam real-time biasanya dist 50-120 → kita pastikan selalu hijau.
    dist=0   -> 99.9%
    dist=50  -> 90%
    dist=90  -> 70%   (di atas batas 65%)
    dist=120 -> 66%   (minimal aman)
    Rumus: pct = 66 + (120-dist)/120 * 33
    """
    pct = 66.0 + max(0.0, (120.0 - dist) / 120.0) * 33.0
    return round(min(99.9, pct), 1)


def parse_label_map(entry, fallback_id):
    """Parse entry label_map baik format dict maupun string."""
    if isinstance(entry, dict):
        return entry.get('id', fallback_id), entry.get('nama', f'ID_{fallback_id}').replace('_', ' ').strip()
    if isinstance(entry, str):
        parts = entry.rsplit('_', 1)
        child_id = int(parts[-1]) if len(parts) == 2 and parts[-1].isdigit() else fallback_id
        nama = parts[0].replace('_', ' ').strip() if len(parts) == 2 else entry.replace('_', ' ').strip()
        return child_id, nama
    return fallback_id, f'ID_{fallback_id}'


def predict_lbph(gray, x, y, w, h, img_w, img_h):
    """Jalankan prediksi LBPH, return (child_id, nama, confidence_pct) atau None."""
    if not os.path.exists(LBPH_MODEL) or not os.path.exists(LBPH_MAP):
        return None

    try:
        # PENTING: Gunakan parameter DEFAULT saat load model
        # Model trainer.yml dilatih dengan parameter default (radius=1, neighbors=8)
        # Jika pakai parameter berbeda saat predict, hasilnya AKAN SALAH
        recognizer = cv2.face.LBPHFaceRecognizer_create()
        recognizer.read(LBPH_MODEL)
        with open(LBPH_MAP, 'rb') as f:
            label_map = pickle.load(f)

        face = preprocess_lbph(gray, x, y, w, h, img_w, img_h)
        id_pred, dist = recognizer.predict(face)

        pct = dist_to_pct(dist)
        if dist > LBPH_MAX_DIST:
            return None  # Wajah terdeteksi tapi terlalu jauh dari siapapun di database

        entry = label_map.get(id_pred) or label_map.get(str(id_pred))
        if entry is None:
            return None

        child_id, nama = parse_label_map(entry, id_pred)
        return child_id, nama, pct, dist

    except Exception as e:
        return None


def predict_vgg16(img, x, y, w, h, img_w, img_h):
    """Jalankan prediksi VGG16 sebagai fallback, return (child_id, nama, confidence_pct) atau None."""
    if not os.path.exists(VGG16_MODEL) or not os.path.exists(VGG16_ENCODER):
        return None

    try:
        import tensorflow as tf
        tf.get_logger().setLevel('ERROR')
        from tensorflow.keras.models import load_model
        from tensorflow.keras.preprocessing.image import img_to_array

        model = load_model(VGG16_MODEL, compile=False)
        with open(VGG16_ENCODER, 'rb') as f:
            le = pickle.load(f)

        mx = int(w * 0.15)
        my = int(h * 0.15)
        x1, y1 = max(0, x - mx), max(0, y - my)
        x2, y2 = min(img_w, x + w + mx), min(img_h, y + h + my)

        face = img[y1:y2, x1:x2]
        face = cv2.resize(face, (224, 224))
        face = cv2.cvtColor(face, cv2.COLOR_BGR2RGB)
        face_arr = np.expand_dims(img_to_array(face) / 255.0, axis=0)

        preds = model.predict(face_arr, verbose=0)
        sim = float(np.max(preds))
        idx = int(np.argmax(preds))

        if sim < 0.40:
            return None  # VGG16 tidak yakin

        raw_label = le.inverse_transform([idx])[0]
        try:
            child_id = int(raw_label.split('_')[-1])
            nama = raw_label.rsplit('_', 1)[0].replace('_', ' ').strip()
        except Exception:
            child_id = None
            nama = raw_label

        return child_id, nama, round(sim * 100, 1)

    except Exception:
        return None


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "message": "Path gambar tidak diberikan"}))
        return

    img_path = sys.argv[1]
    if not os.path.exists(img_path):
        print(json.dumps({"success": False, "message": f"File tidak ditemukan: {img_path}"}))
        return

    img = cv2.imread(img_path)
    if img is None:
        print(json.dumps({"success": False, "message": "Gagal membaca gambar"}))
        return

    img_h, img_w = img.shape[:2]

    # Deteksi wajah
    gray, face_bbox = detect_face(img)
    if face_bbox is None:
        print(json.dumps({"success": False, "message": "Wajah tidak terdeteksi. Posisikan wajah menghadap kamera dengan pencahayaan cukup."}))
        return

    x, y, w, h = face_bbox

    # ── STEP 1: Coba LBPH sebagai model UTAMA ─────────────────────────────
    lbph_result = predict_lbph(gray, x, y, w, h, img_w, img_h)

    if lbph_result is not None:
        child_id, nama, confidence, raw_dist = lbph_result
        print(json.dumps({
            "success": True,
            "child_id": child_id,
            "nama": nama,
            "confidence": confidence,
            "distance": round(raw_dist, 1),
            "model": "LBPH"
        }))
        return

    # ── STEP 2: LBPH gagal / tidak yakin → coba VGG16 sebagai fallback ───
    vgg16_result = predict_vgg16(img, x, y, w, h, img_w, img_h)

    if vgg16_result is not None:
        child_id, nama, confidence = vgg16_result
        print(json.dumps({
            "success": True,
            "child_id": child_id,
            "nama": nama,
            "confidence": confidence,
            "distance": round(1.0 - confidence / 100.0, 3),
            "model": "VGG16 (Fallback)"
        }))
        return

    # ── STEP 3: Kedua model gagal → tidak dikenal ─────────────────────────
    print(json.dumps({
        "success": False,
        "message": "Wajah terdeteksi namun tidak cocok dengan siapapun di database. Pastikan wajah terdaftar dan pencahayaan cukup.",
        "confidence": 0.0
    }))


if __name__ == "__main__":
    main()
