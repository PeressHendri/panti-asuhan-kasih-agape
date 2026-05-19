"""
Update LBPH Semua Anak — Gunakan Foto dari Captures + Database Mapping
======================================================================
Dipanggil oleh artisan command: php artisan face:update-lbph

Argumen:
  sys.argv[1] = path ke file JSON mapping {foto_path: child_db_id}

Yang dilakukan:
  1. Load model LBPH yang sudah ada
  2. Baca mapping foto → child_id dari JSON
  3. Konversi child_id ke LBPH label index via label_map
  4. Deteksi & preprocess wajah dari setiap foto
  5. Update model secara incremental (recognizer.update)
  6. Simpan model baru
"""

import os, sys, cv2, json, pickle, shutil
from pathlib import Path
from collections import defaultdict
import numpy as np

if len(sys.argv) < 2:
    print("[ERROR] Argumen mapping JSON tidak diberikan.")
    sys.exit(1)

MAPPING_FILE = Path(sys.argv[1])
if not MAPPING_FILE.exists():
    print(f"[ERROR] File mapping tidak ditemukan: {MAPPING_FILE}")
    sys.exit(1)

BASE_DIR   = Path(__file__).parent.parent
LBPH_MODEL = BASE_DIR / "models" / "lbph" / "trainer.yml"
LBPH_MAP   = BASE_DIR / "models" / "lbph" / "label_map.pkl"

# ── Cek file model ─────────────────────────────────────────────────────────
if not LBPH_MODEL.exists():
    print(f"[ERROR] Model tidak ditemukan: {LBPH_MODEL}")
    sys.exit(1)

if not LBPH_MAP.exists():
    print(f"[ERROR] Label map tidak ditemukan: {LBPH_MAP}")
    sys.exit(1)

# ── Load label_map: child_db_id → lbph_index ──────────────────────────────
with open(LBPH_MAP, 'rb') as f:
    label_map = pickle.load(f)

# Buat reverse map: child_db_id → lbph_index
child_id_to_lbph = {}
for lbph_idx, entry in label_map.items():
    if isinstance(entry, dict):
        cid  = entry.get('id')
        nama = entry.get('nama', f'ID_{lbph_idx}')
    elif isinstance(entry, str):
        parts = entry.rsplit('_', 1)
        cid   = int(parts[-1]) if len(parts) == 2 and parts[-1].isdigit() else None
        nama  = parts[0].replace('_', ' ') if len(parts) == 2 else entry
    else:
        cid, nama = None, str(entry)

    if cid is not None:
        child_id_to_lbph[cid] = (lbph_idx, nama)

# ── Load mapping foto → child_db_id ───────────────────────────────────────
with open(MAPPING_FILE, 'r') as f:
    photo_mapping = json.load(f)   # {"/absolute/path.jpg": child_db_id}

# ── Preprocessing ─────────────────────────────────────────────────────────
cascade     = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
cascade_alt = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_alt2.xml')

def preprocess_face(img_path):
    img = cv2.imread(str(img_path))
    if img is None:
        return None
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    for cas, sf, mn in [(cascade, 1.05, 4), (cascade_alt, 1.05, 3), (cascade, 1.10, 3)]:
        faces = cas.detectMultiScale(gray, scaleFactor=sf, minNeighbors=mn, minSize=(40, 40))
        if len(faces) > 0:
            x, y, w, h = sorted(faces, key=lambda f: f[2]*f[3], reverse=True)[0]
            ih, iw = gray.shape
            mx, my = int(w*0.12), int(h*0.12)
            face   = gray[max(0,y-my):min(ih,y+h+my), max(0,x-mx):min(iw,x+w+mx)]
            face   = cv2.resize(face, (200, 200), interpolation=cv2.INTER_LANCZOS4)
            clahe  = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
            face   = clahe.apply(face)
            face   = cv2.GaussianBlur(face, (3, 3), 0)
            face   = cv2.equalizeHist(face)
            return face
    return None

# ── Proses setiap foto ────────────────────────────────────────────────────
print(f"\n  Memproses {len(photo_mapping)} foto...")
print(f"  {'─'*50}")

all_faces  = []
all_labels = []
stats      = defaultdict(lambda: {'ok': 0, 'skip_noface': 0, 'skip_nomap': 0})

for photo_path, child_db_id in photo_mapping.items():
    child_db_id = int(child_db_id)

    if child_db_id not in child_id_to_lbph:
        stats[child_db_id]['skip_nomap'] += 1
        continue

    lbph_idx, nama = child_id_to_lbph[child_db_id]
    face = preprocess_face(photo_path)

    if face is None:
        stats[child_db_id]['skip_noface'] += 1
        continue

    all_faces.append(face)
    all_labels.append(lbph_idx)
    stats[child_db_id]['ok'] += 1

# ── Ringkasan per anak ────────────────────────────────────────────────────
print(f"\n  Ringkasan per anak:")
for child_db_id, s in sorted(stats.items()):
    if child_db_id in child_id_to_lbph:
        _, nama = child_id_to_lbph[child_db_id]
    else:
        nama = f'child_id={child_db_id}'
    total = s['ok'] + s['skip_noface'] + s['skip_nomap']
    print(f"  {nama:<35} | {s['ok']:2d} OK, {s['skip_noface']} no-face, {s['skip_nomap']} no-map  (total {total})")

print(f"\n  Total foto berhasil diproses: {len(all_faces)}")

if len(all_faces) < 5:
    print("\n[!] Terlalu sedikit foto yang berhasil diproses.")
    print("    Pastikan anak-anak sudah pernah berhasil absensi wajah sebelumnya.")
    sys.exit(1)

# ── Update model LBPH ─────────────────────────────────────────────────────
print(f"\n  Loading model LBPH yang ada...")
recognizer = cv2.face.LBPHFaceRecognizer_create()   # HARUS default params
recognizer.read(str(LBPH_MODEL))

print(f"  Menambahkan {len(all_faces)} foto baru ke model (incremental update)...")
recognizer.update(all_faces, np.array(all_labels, dtype=np.int32))

# Backup dan simpan
backup = str(LBPH_MODEL) + f".bak"
shutil.copy2(LBPH_MODEL, backup)
recognizer.save(str(LBPH_MODEL))
print(f"  Model diperbarui: {LBPH_MODEL}")
print(f"  Backup tersimpan: {backup}")

# ── Verifikasi cepat ──────────────────────────────────────────────────────
print(f"\n  Verifikasi cepat (sample 30 foto)...")
recognizer2 = cv2.face.LBPHFaceRecognizer_create()
recognizer2.read(str(LBPH_MODEL))

sample_faces  = all_faces[:30]
sample_labels = all_labels[:30]
correct = 0

for face, true_label in zip(sample_faces, sample_labels):
    pred_label, dist = recognizer2.predict(face)
    if pred_label == true_label:
        correct += 1

acc = correct / len(sample_faces) * 100 if sample_faces else 0
print(f"  Akurasi self-test: {correct}/{len(sample_faces)} = {acc:.0f}%")

print(f"\n{'='*55}")
if acc >= 60:
    print(f"  ✓ UPDATE BERHASIL! Model sudah diperbarui.")
    print(f"    Coba absensi wajah sekarang.")
else:
    print(f"  [!] Akurasi masih rendah ({acc:.0f}%).")
    print(f"    Mungkin foto captures belum cukup variatif.")
print(f"{'='*55}\n")
sys.exit(0)
