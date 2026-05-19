"""
Update LBPH Untuk Peres — Tanpa Upload Dataset Lengkap
======================================================
Script ini HANYA menambah foto Peres ke model LBPH yang sudah ada
menggunakan metode recognizer.update() — tidak perlu retrain dari nol.

Cara pakai di VPS:
  cd ~/htdocs/pantiasuhankasihagape.id
  python3 recognition_engine/scripts/update_peres_lbph.py

Yang dilakukan:
  1. Load model LBPH yang sudah ada (trainer.yml)
  2. Ambil foto terbaru dari folder captures (foto Peres yang gagal scan)
  3. Deteksi wajah di setiap foto
  4. Tambahkan ke model dengan label index 18 (= Peres, child_id=21)
  5. Simpan model yang sudah diperbarui
"""

import os, sys, cv2, glob, pickle, shutil
from pathlib import Path
import numpy as np

BASE_DIR    = Path(__file__).parent.parent
LBPH_MODEL  = BASE_DIR / "models" / "lbph" / "trainer.yml"
LBPH_MAP    = BASE_DIR / "models" / "lbph" / "label_map.pkl"
CAPTURE_DIR = BASE_DIR.parent / "storage" / "app" / "public" / "captures"

# ──────────────────────────────────────────────────────────────────────────
# Cek model dan label_map tersedia
# ──────────────────────────────────────────────────────────────────────────
if not LBPH_MODEL.exists():
    print(f"[ERROR] Model tidak ditemukan: {LBPH_MODEL}")
    sys.exit(1)

if not LBPH_MAP.exists():
    print(f"[ERROR] Label map tidak ditemukan: {LBPH_MAP}")
    sys.exit(1)

with open(LBPH_MAP, 'rb') as f:
    label_map = pickle.load(f)

# Cari index LBPH untuk Peres
PERES_LBPH_IDX = None
PERES_CHILD_ID = None
PERES_NAMA     = None

for idx, entry in label_map.items():
    nama = entry.get('nama', '') if isinstance(entry, dict) else str(entry)
    if 'Peres' in nama or 'peres' in nama.lower():
        PERES_LBPH_IDX = idx
        PERES_CHILD_ID = entry.get('id', idx) if isinstance(entry, dict) else idx
        PERES_NAMA     = nama
        break

if PERES_LBPH_IDX is None:
    print("[ERROR] Tidak menemukan 'Peres' di label_map!")
    print("Isi label_map yang ada:")
    for k, v in label_map.items():
        print(f"  {k}: {v}")
    sys.exit(1)

print(f"\n{'='*55}")
print(f"  UPDATE LBPH UNTUK PERES HENDRI")
print(f"{'='*55}")
print(f"  LBPH index : {PERES_LBPH_IDX}")
print(f"  child_id   : {PERES_CHILD_ID}")
print(f"  Nama       : {PERES_NAMA}")
print(f"  Model      : {LBPH_MODEL}")

# ──────────────────────────────────────────────────────────────────────────
# Preprocessing wajah (HARUS IDENTIK dengan recognize_cnn.py)
# ──────────────────────────────────────────────────────────────────────────
cascade     = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
cascade_alt = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_alt2.xml')

def preprocess(img_path):
    img = cv2.imread(str(img_path))
    if img is None:
        return None
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    for cas, sf, mn in [(cascade, 1.05, 4), (cascade_alt, 1.05, 3), (cascade, 1.10, 3)]:
        faces = cas.detectMultiScale(gray, scaleFactor=sf, minNeighbors=mn, minSize=(40, 40))
        if len(faces) > 0:
            x, y, w, h = sorted(faces, key=lambda f: f[2]*f[3], reverse=True)[0]
            ih, iw = gray.shape
            mx, my  = int(w*0.12), int(h*0.12)
            face    = gray[max(0,y-my):min(ih,y+h+my), max(0,x-mx):min(iw,x+w+mx)]
            face    = cv2.resize(face, (200, 200), interpolation=cv2.INTER_LANCZOS4)
            clahe   = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
            face    = clahe.apply(face)
            face    = cv2.GaussianBlur(face, (3, 3), 0)
            face    = cv2.equalizeHist(face)
            return face
    return None

# ──────────────────────────────────────────────────────────────────────────
# Kumpulkan foto dari folder captures
# ──────────────────────────────────────────────────────────────────────────
if not CAPTURE_DIR.exists():
    print(f"\n[ERROR] Folder captures tidak ditemukan: {CAPTURE_DIR}")
    sys.exit(1)

all_captures = sorted(CAPTURE_DIR.glob('web_*.jpg'), key=os.path.getmtime, reverse=True)
print(f"\n  Mencari foto di captures: {len(all_captures)} file ditemukan")
print(f"  Memeriksa 30 foto terbaru...\n")

new_faces  = []
new_labels = []

for img_path in all_captures[:30]:  # Cek 30 terbaru
    face = preprocess(img_path)
    if face is not None:
        new_faces.append(face)
        new_labels.append(PERES_LBPH_IDX)
        print(f"  ✓ Wajah terdeteksi : {img_path.name}")
    else:
        print(f"  ✗ Tidak ada wajah  : {img_path.name}")

print(f"\n  {len(new_faces)} foto berhasil diproses dari {min(30, len(all_captures))} capture terbaru")

if len(new_faces) < 3:
    print("\n[!] Terlalu sedikit foto wajah (<3). Coba absensi beberapa kali lagi tanpa kacamata dulu.")
    print("    Lalu jalankan script ini lagi.")
    sys.exit(1)

# ──────────────────────────────────────────────────────────────────────────
# Update model LBPH dengan foto baru Peres
# ──────────────────────────────────────────────────────────────────────────
print(f"\n  Memuat model LBPH yang ada...")
recognizer = cv2.face.LBPHFaceRecognizer_create()  # default params
recognizer.read(str(LBPH_MODEL))

print(f"  Menambahkan {len(new_faces)} foto Peres ke model (update incremental)...")
recognizer.update(new_faces, np.array(new_labels, dtype=np.int32))

# Backup model lama
backup_path = str(LBPH_MODEL) + ".backup"
shutil.copy2(LBPH_MODEL, backup_path)
print(f"  Backup model lama : {backup_path}")

# Simpan model baru
recognizer.save(str(LBPH_MODEL))
print(f"  Model baru disimpan : {LBPH_MODEL}")

# ──────────────────────────────────────────────────────────────────────────
# Verifikasi — uji model baru pada foto-foto yang baru ditambahkan
# ──────────────────────────────────────────────────────────────────────────
print(f"\n  Verifikasi model baru...")
recognizer2 = cv2.face.LBPHFaceRecognizer_create()
recognizer2.read(str(LBPH_MODEL))

correct = 0
for face, true_label in zip(new_faces, new_labels):
    pred_label, dist = recognizer2.predict(face)
    pred_nama        = label_map.get(pred_label, {})
    pred_nama_str    = pred_nama.get('nama', f'ID_{pred_label}') if isinstance(pred_nama, dict) else str(pred_nama)
    status = '✓' if pred_label == true_label else '✗'
    if pred_label == true_label:
        correct += 1
    print(f"  {status} Prediksi: {pred_nama_str:<30} (dist={dist:.1f})")

acc = correct / len(new_faces) * 100
print(f"\n  Akurasi pada foto baru: {correct}/{len(new_faces)} = {acc:.0f}%")

print(f"\n{'='*55}")
if acc >= 70:
    print(f"  ✓ UPDATE BERHASIL! Coba absensi wajah sekarang.")
else:
    print(f"  [!] Akurasi masih rendah ({acc:.0f}%). Coba lagi setelah absen beberapa kali lagi")
    print(f"      (agar lebih banyak foto variasi yang masuk ke captures).")
print(f"{'='*55}\n")
