"""
Script Retrain LBPH — Panti Asuhan Kasih Agape
===============================================
Cara pemakaian di VPS:
  python3 train_lbph.py

Apa yang dilakukan:
  1. Membaca semua foto dari folder dataset/train/<Nama_ID>/
  2. Mendeteksi wajah di setiap foto
  3. Melatih ulang model LBPH dari awal
  4. Menyimpan trainer.yml + label_map.pkl yang baru
"""

import os, sys, json, pickle, cv2, numpy as np
from pathlib import Path

BASE_DIR    = Path(__file__).parent.parent
DATASET_DIR = BASE_DIR / "dataset" / "train"
OUTPUT_DIR  = BASE_DIR / "models" / "lbph"

# ─── Pastikan ada data ────────────────────────────────────────────────────
if not DATASET_DIR.exists():
    print(f"[ERROR] Folder dataset tidak ditemukan: {DATASET_DIR}")
    print("Buat folder dan isi dengan subfolder bernama: Nama_ID (cth: Peres_Hendri_Virgiawan_21)")
    sys.exit(1)

OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

# ─── Deteksi wajah ───────────────────────────────────────────────────────
cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
cascade_alt = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_alt2.xml')

def detect_and_preprocess(img_path):
    img  = cv2.imread(str(img_path))
    if img is None:
        return None
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    for cas, sf, mn in [(cascade, 1.05, 4), (cascade_alt, 1.05, 3), (cascade, 1.10, 3)]:
        faces = cas.detectMultiScale(gray, scaleFactor=sf, minNeighbors=mn, minSize=(40, 40))
        if len(faces) > 0:
            x, y, w, h = sorted(faces, key=lambda f: f[2]*f[3], reverse=True)[0]
            ih, iw = gray.shape
            mx, my = int(w*0.12), int(h*0.12)
            face = gray[max(0,y-my):min(ih,y+h+my), max(0,x-mx):min(iw,x+w+mx)]
            face = cv2.resize(face, (200, 200), interpolation=cv2.INTER_LANCZOS4)
            clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
            face  = clahe.apply(face)
            face  = cv2.GaussianBlur(face, (3, 3), 0)
            face  = cv2.equalizeHist(face)
            return face
    return None

# ─── Kumpulkan data training ──────────────────────────────────────────────
faces_data   = []
labels_data  = []
label_map    = {}    # lbph_index → {'id': child_db_id, 'nama': 'Nama Lengkap'}
lbph_idx     = 0

folders = sorted([d for d in DATASET_DIR.iterdir() if d.is_dir()])
if not folders:
    print(f"[ERROR] Tidak ada subfolder di {DATASET_DIR}")
    sys.exit(1)

print(f"\n{'='*55}")
print(f"  LBPH RETRAIN — {len(folders)} kelas ditemukan")
print(f"{'='*55}")

for folder in folders:
    folder_name = folder.name            # contoh: Peres_Hendri_Virgiawan_21
    parts       = folder_name.rsplit('_', 1)
    if len(parts) == 2 and parts[1].isdigit():
        child_db_id = int(parts[1])
        nama_raw    = parts[0]
    else:
        # Fallback: gunakan nama folder utuh
        child_db_id = lbph_idx + 1
        nama_raw    = folder_name

    nama_display = nama_raw.replace('_', ' ').strip()

    img_paths = sorted(list(folder.glob('*.jpg')) +
                       list(folder.glob('*.jpeg')) +
                       list(folder.glob('*.png')))

    ok, skip = 0, 0
    for img_path in img_paths:
        face = detect_and_preprocess(img_path)
        if face is not None:
            faces_data.append(face)
            labels_data.append(lbph_idx)
            ok += 1
        else:
            skip += 1

    label_map[lbph_idx] = {'id': child_db_id, 'nama': nama_display}
    status = '✓' if ok > 0 else '✗'
    print(f"  {status} [{lbph_idx:2d}] {nama_display:<35} id={child_db_id:<3} | {ok} foto OK, {skip} skip")
    lbph_idx += 1

print(f"\n  Total: {len(faces_data)} wajah dari {lbph_idx} kelas\n")

if len(faces_data) == 0:
    print("[ERROR] Tidak ada wajah yang berhasil dideteksi! Cek kualitas foto dataset.")
    sys.exit(1)

# ─── Latih model LBPH ────────────────────────────────────────────────────
print("  Melatih model LBPH...", end=' ', flush=True)
recognizer = cv2.face.LBPHFaceRecognizer_create()  # default params (radius=1, neighbors=8)
recognizer.train(faces_data, np.array(labels_data, dtype=np.int32))

model_path   = OUTPUT_DIR / "trainer.yml"
map_path     = OUTPUT_DIR / "label_map.pkl"
backup_path  = OUTPUT_DIR / "label_map.pkl.backup"

# Backup label_map lama
if map_path.exists():
    import shutil
    shutil.copy(map_path, backup_path)

recognizer.save(str(model_path))
with open(map_path, 'wb') as f:
    pickle.dump(label_map, f)

print("SELESAI!")
print(f"\n  Model disimpan di : {model_path}")
print(f"  Label map         : {map_path}")
print(f"\n{'='*55}")
print("  RETRAIN BERHASIL! Coba absensi wajah sekarang.")
print(f"{'='*55}\n")

# ─── Verifikasi cepat ─────────────────────────────────────────────────────
print("  Verifikasi cepat (self-test pada data training)...")
recognizer2 = cv2.face.LBPHFaceRecognizer_create()
recognizer2.read(str(model_path))
correct, total = 0, 0
for true_idx, face in zip(labels_data[:min(50, len(labels_data))], faces_data[:50]):
    pred_idx, dist = recognizer2.predict(face)
    if pred_idx == true_idx:
        correct += 1
    total += 1

acc = correct / total * 100 if total > 0 else 0
print(f"  Akurasi self-test : {correct}/{total} = {acc:.1f}%")
if acc < 80:
    print("  [!] Akurasi rendah. Pastikan foto berkualitas baik dan wajah terlihat jelas.")
else:
    print("  Model terlatih dengan baik!")
print()
