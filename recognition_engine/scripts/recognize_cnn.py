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
BASE_DIR    = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
LBPH_MODEL  = os.path.join(BASE_DIR, "models", "lbph", "trainer.yml")
LBPH_MAP    = os.path.join(BASE_DIR, "models", "lbph", "label_map.pkl")

# TIDAK ADA threshold penolakan — LBPH selalu memberikan kandidat terbaik.
# Sistem membiarkan confidence mapping yang menentukan "yakin / tidak yakin"
# sehingga score akhir di UI bisa menjadi informasi, bukan blokir.


def detect_face(img):
    """Deteksi wajah terbesar menggunakan 2 cascade secara berurutan."""
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    params = [
        ('haarcascade_frontalface_default.xml', 1.05, 4),
        ('haarcascade_frontalface_alt2.xml',    1.05, 3),
        ('haarcascade_frontalface_default.xml', 1.10, 3),  # fallback lebih longgar
    ]
    for name, sf, mn in params:
        cas   = cv2.CascadeClassifier(cv2.data.haarcascades + name)
        faces = cas.detectMultiScale(gray, scaleFactor=sf, minNeighbors=mn, minSize=(40, 40))
        if len(faces) > 0:
            faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
            return gray, faces[0]
    return gray, None


def preprocess_lbph(gray, x, y, w, h, img_w, img_h):
    """Preprocessing wajah optimal untuk LBPH — identik dengan saat training."""
    mx = int(w * 0.12)
    my = int(h * 0.12)
    x1, y1 = max(0, x - mx), max(0, y - my)
    x2, y2 = min(img_w, x + w + mx), min(img_h, y + h + my)
    face = gray[y1:y2, x1:x2]
    face = cv2.resize(face, (200, 200), interpolation=cv2.INTER_LANCZOS4)
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
    face  = clahe.apply(face)
    face  = cv2.GaussianBlur(face, (3, 3), 0)
    face  = cv2.equalizeHist(face)
    return face


def dist_to_confidence(dist):
    """
    Mapping LBPH distance → confidence % (selalu >= 60%).
    Inti: berapapun distance-nya, selama wajah terdeteksi dan LBPH memberi kandidat,
    hasilnya DITERIMA. Confidence hanya sebagai informasi di UI, bukan blokir.

    Skala:
      dist <=  30  → 99%
      dist  =  60  → 88%
      dist  =  90  → 77%
      dist  = 120  → 66%
      dist >= 150  → 60%
    """
    if dist <= 30:
        return 99.0
    if dist >= 150:
        return 60.0
    # Interpolasi linear antara (30→99) dan (150→60)
    pct = 99.0 - ((dist - 30.0) / (150.0 - 30.0)) * (99.0 - 60.0)
    return round(pct, 1)


def parse_label_map(entry, fallback_id):
    if isinstance(entry, dict):
        return entry.get('id', fallback_id), entry.get('nama', f'ID_{fallback_id}').replace('_', ' ').strip()
    if isinstance(entry, str):
        parts  = entry.rsplit('_', 1)
        cid    = int(parts[-1]) if len(parts) == 2 and parts[-1].isdigit() else fallback_id
        nama   = parts[0].replace('_', ' ').strip() if len(parts) == 2 else entry.replace('_', ' ').strip()
        return cid, nama
    return fallback_id, f'ID_{fallback_id}'


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

    # ── Deteksi Wajah ─────────────────────────────────────────────────────
    gray, face_bbox = detect_face(img)
    if face_bbox is None:
        print(json.dumps({
            "success": False,
            "message": "Wajah tidak terdeteksi. Posisikan wajah menghadap kamera."
        }))
        return

    x, y, w, h = face_bbox

    # ── Cek model LBPH tersedia ───────────────────────────────────────────
    if not os.path.exists(LBPH_MODEL) or not os.path.exists(LBPH_MAP):
        print(json.dumps({
            "success": False,
            "message": "Model LBPH tidak ditemukan di server. Hubungi administrator."
        }))
        return

    try:
        # Load LBPH — WAJIB pakai parameter default (sesuai saat training)
        recognizer = cv2.face.LBPHFaceRecognizer_create()
        recognizer.read(LBPH_MODEL)

        with open(LBPH_MAP, 'rb') as f:
            label_map = pickle.load(f)

        face         = preprocess_lbph(gray, x, y, w, h, img_w, img_h)
        id_pred, dist = recognizer.predict(face)

        # ── THRESHOLD PENOLAKAN ───────────────────────────────────────────
        # JIKA model ragu (distance > 90), tolak! Ini mencegah 1 wajah 
        # terdeteksi sebagai banyak anak (halusinasi model).
        if dist > 90:
            print(json.dumps({
                "success": False,
                "message": f"Wajah terdeteksi namun belum dikenali (distance {round(dist, 1)} > 90)."
            }))
            return

        entry = label_map.get(id_pred) or label_map.get(str(id_pred))
        if entry is None:
            print(json.dumps({
                "success": False,
                "message": f"Label index {id_pred} tidak ditemukan di database model."
            }))
            return

        child_id, nama = parse_label_map(entry, id_pred)
        
        # Konversi dist ke % (hanya untuk tampilan visual di web)
        confidence = 99.0 - ((dist - 30.0) / (90.0 - 30.0)) * (99.0 - 65.0)
        confidence = round(max(65.0, min(99.0, confidence)), 1)

        print(json.dumps({
            "success":    True,
            "child_id":   child_id,
            "nama":       nama,
            "confidence": confidence,
            "distance":   round(dist, 1),
            "model":      "LBPH"
        }))

    except Exception as e:
        print(json.dumps({"success": False, "message": f"Error prediksi LBPH: {str(e)}"}))


if __name__ == "__main__":
    main()
