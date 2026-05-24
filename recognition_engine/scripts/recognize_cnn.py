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
if BASE_DIR not in sys.path:
    sys.path.insert(0, BASE_DIR)

from utils.face_detector import detect_faces_dnn
from scripts.predict_hybrid import predict_fusion

LBPH_MODEL  = os.path.join(BASE_DIR, "models", "lbph", "trainer.yml")
LBPH_MAP    = os.path.join(BASE_DIR, "models", "lbph", "label_map.pkl")
VGG16_MODEL = os.path.join(BASE_DIR, "models", "vgg16", "model_vgg16_adam.h5")
VGG16_ENC   = os.path.join(BASE_DIR, "models", "vgg16", "label_encoder.pkl")


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

    # ── Deteksi Wajah dengan DNN ──────────────────────────────────────────
    faces = detect_faces_dnn(img)
    if not faces:
        print(json.dumps({
            "success": False,
            "message": "Wajah tidak terdeteksi. Posisikan wajah menghadap kamera."
        }))
        return

    # Ambil wajah terbesar
    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    x, y, w, h = faces[0]

    # ── Load Model Hybrid ─────────────────────────────────────────────────
    model_vgg = None
    le_vgg = None
    recognizer = None
    label_map = {}

    try:
        from tensorflow.keras.models import load_model
        if os.path.exists(VGG16_MODEL) and os.path.exists(VGG16_ENC):
            model_vgg = load_model(VGG16_MODEL)
            with open(VGG16_ENC, 'rb') as f:
                le_vgg = pickle.load(f)
    except Exception:
        pass # Skip VGG jika gagal load, fallback ke LBPH

    if os.path.exists(LBPH_MODEL) and os.path.exists(LBPH_MAP):
        try:
            recognizer = cv2.face.LBPHFaceRecognizer_create()
            recognizer.read(LBPH_MODEL)
            with open(LBPH_MAP, 'rb') as f:
                label_map = pickle.load(f)
        except Exception:
            pass

    if model_vgg is None and recognizer is None:
        print(json.dumps({
            "success": False,
            "message": "Gagal meload semua model AI di server."
        }))
        return

    try:
        is_recognized, nama, child_id, conf, model_used = predict_fusion(
            img, x, y, w, h, model_vgg, le_vgg, recognizer, label_map, confidence_threshold=75
        )

        if not is_recognized and conf < 50:
             print(json.dumps({
                 "success": False,
                 "message": f"Wajah terdeteksi namun belum dikenali."
             }))
             return

        print(json.dumps({
            "success":    True,
            "child_id":   child_id,
            "nama":       nama,
            "confidence": round(conf, 1),
            "distance":   0, # Deprecated
            "model":      model_used
        }))

    except Exception as e:
        print(json.dumps({"success": False, "message": f"Error prediksi: {str(e)}"}))

if __name__ == "__main__":
    main()