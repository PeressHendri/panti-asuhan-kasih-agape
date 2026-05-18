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
        # Muat Model VGG16 dan Encoder
        model = load_model(VGG16_MODEL_PATH)
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
        # Preprocessing Wajah
        face_roi = img[y:y+h, x:x+w]
        face_resized = cv2.resize(face_roi, (224, 224))
        face_array = img_to_array(face_resized) / 255.0
        face_array = np.expand_dims(face_array, axis=0)

        # Prediksi dengan VGG16
        preds = model.predict(face_array, verbose=0)
        similarity = float(np.max(preds))
        
        if similarity < VGG16_SIM_THRESHOLD:
            print(json.dumps({
                "success": False,
                "message": "Wajah terdeteksi tapi tingkat kecocokan model VGG16 di bawah threshold.",
                "confidence": round(similarity * 100, 1)
            }))
            return

        # Ambil Label Hasil Prediksi
        label_idx = int(np.argmax(preds))
        raw_label = le.inverse_transform([label_idx])[0]

        try:
            # Parse label: format "Nama_Lengkap_ID"
            child_id = int(raw_label.split("_")[-1])
            nama = raw_label.rsplit('_', 1)[0].replace('_', ' ')
        except Exception:
            child_id = None
            nama = raw_label

        print(json.dumps({
            "success": True,
            "child_id": child_id,
            "nama": nama,
            "confidence": round(similarity * 100, 1),
            "distance": round(1.0 - similarity, 3),
            "model": "VGG16 Custom"
        }))

    except Exception as e:
        print(json.dumps({"success": False, "message": f"Error selama inferensi VGG16: {str(e)}"}))

if __name__ == "__main__":
    main()
