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
        # Preprocessing Wajah
        face_roi = img[y:y+h, x:x+w]
        face_resized = cv2.resize(face_roi, (224, 224))
        
        # Karena Keras biasanya di-training menggunakan RGB, sedangkan OpenCV menangkap BGR,
        # kita akan menguji kedua format warna dan mengambil hasil dengan akurasi tertinggi!
        
        # 1. Uji dengan format BGR (Bawaan OpenCV)
        face_bgr = img_to_array(face_resized) / 255.0
        face_bgr = np.expand_dims(face_bgr, axis=0)
        preds_bgr = model.predict(face_bgr, verbose=0)
        sim_bgr = float(np.max(preds_bgr))
        
        # 2. Uji dengan format RGB (Bawaan Keras/TensorFlow saat training)
        face_rgb_img = cv2.cvtColor(face_resized, cv2.COLOR_BGR2RGB)
        face_rgb = img_to_array(face_rgb_img) / 255.0
        face_rgb = np.expand_dims(face_rgb, axis=0)
        preds_rgb = model.predict(face_rgb, verbose=0)
        sim_rgb = float(np.max(preds_rgb))
        
        # Ambil hasil yang paling meyakinkan (menghindari bug warna biru/merah terbalik)
        if sim_rgb > sim_bgr:
            preds = preds_rgb
            similarity = sim_rgb
        else:
            preds = preds_bgr
            similarity = sim_bgr

        # Threshold diturunkan menjadi 0.15 agar lebih toleran terhadap perbedaan cahaya webcam
        VGG16_SIM_THRESHOLD_RELAXED = 0.15 
        
        if similarity < VGG16_SIM_THRESHOLD_RELAXED:
            print(json.dumps({
                "success": False,
                "message": f"Wajah terdeteksi tapi kecocokan sangat rendah ({round(similarity * 100, 1)}%). Coba hadapkan wajah ke terang.",
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
