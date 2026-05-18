import os
import sys
import json
import logging

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
    import pandas as pd
    from deepface import DeepFace
except ImportError as e:
    print(json.dumps({"success": False, "message": f"Library Error: {str(e)} (Pastikan install opencv-python-headless di VPS)"}))
    sys.exit(1)

# Matikan log DeepFace
logging.getLogger("deepface").setLevel(logging.ERROR)

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
# Dataset folder dimana foto anak disimpan per folder ID, cth: dataset/24/foto.jpg
DATASET_PATH = os.path.join(BASE_DIR, "dataset")

# Menggunakan model FaceNet karena akurasi tinggi dan lumayan cepat di CPU
MODEL_NAME = "Facenet" 
# Metric cosine sangat baik untuk perbandingan kemiripan wajah
DISTANCE_METRIC = "cosine"

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "message": "Parameter path gambar tidak diberikan"}))
        return

    img_path = sys.argv[1]
    if not os.path.exists(img_path):
        print(json.dumps({"success": False, "message": f"File gambar tidak ditemukan: {img_path}"}))
        return

    if not os.path.exists(DATASET_PATH):
        print(json.dumps({"success": False, "message": "Folder dataset tidak ditemukan. Pastikan ada foto referensi anak."}))
        return

    try:
        # DeepFace.find otomatis mengekstrak wajah, membandingkan dengan semua foto di db_path.
        # Secara otomatis membuat file .pkl (cache) di folder dataset agar deteksi selanjutnya sangat cepat.
        results = DeepFace.find(
            img_path=img_path, 
            db_path=DATASET_PATH, 
            model_name=MODEL_NAME, 
            distance_metric=DISTANCE_METRIC,
            enforce_detection=True, # Harus ada wajah
            silent=True
        )

        if len(results) > 0 and len(results[0]) > 0:
            df = results[0]
            # Ambil kecocokan terbaik (index 0)
            best_match = df.iloc[0]
            
            # Format return dari DeepFace: path foto yang paling mirip, misal: .../dataset/24_Peres/ref1.jpg
            matched_file_path = best_match['identity']
            distance = best_match[f'{MODEL_NAME}_{DISTANCE_METRIC}'] # Semakin kecil semakin mirip

            # Konversi distance ke persentase akurasi (Cosine: 0 = mirip banget, 1 = tidak mirip)
            # Threshold wajar Cosine untuk Facenet adalah sekitar 0.40
            threshold = 0.40
            if distance > threshold:
                print(json.dumps({
                    "success": False, 
                    "message": "Wajah terdeteksi tapi tidak ada kecocokan yang meyakinkan di database.",
                    "distance": round(distance, 3)
                }))
                return

            accuracy_pct = round((1.0 - (distance / threshold)) * 100, 1)
            accuracy_pct = min(100.0, max(0.0, accuracy_pct))

            # Ekstrak ID anak dari nama folder. 
            # Contoh struktur folder: dataset/24/foto.jpg -> parent folder adalah '24'
            parent_folder_name = os.path.basename(os.path.dirname(matched_file_path))
            
            try:
                # Misal folder dinamai '24' atau '24_Peres', kita ambil angka di depannya
                child_id = int(parent_folder_name.split('_')[0])
            except ValueError:
                child_id = parent_folder_name # Fallback jika string

            print(json.dumps({
                "success": True,
                "child_id": child_id,
                "nama": parent_folder_name.split('_', 1)[1] if '_' in parent_folder_name else f"ID {child_id}",
                "confidence": accuracy_pct,
                "distance": round(distance, 3),
                "model": MODEL_NAME
            }))

        else:
            print(json.dumps({"success": False, "message": "Wajah tidak dikenal (tidak ada kecocokan)."}))

    except ValueError as e:
        # Terjadi jika DeepFace tidak bisa mendeteksi wajah sama sekali di foto
        print(json.dumps({"success": False, "message": "Wajah tidak terlihat jelas di kamera."}))
    except Exception as e:
        print(json.dumps({"success": False, "message": f"Error mesin CNN: {str(e)}"}))

if __name__ == "__main__":
    main()
