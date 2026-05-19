"""
Script Tambah Foto Dataset Peres dari Folder Captures
======================================================
Jalankan di VPS setelah beberapa kali absensi gagal (foto tersimpan di captures).
Script ini akan:
  1. Ambil foto terbaru dari storage/app/public/captures/
  2. Coba deteksi wajah
  3. Tanya apakah foto ini adalah Peres (atau siapa)
  4. Simpan ke folder dataset yang sesuai
  5. Trigger retrain otomatis

Cara pakai di VPS:
  cd ~/htdocs/pantiasuhankasihagape.id
  python3 recognition_engine/scripts/add_training_photos.py
"""

import os, sys, cv2, shutil, glob
from pathlib import Path

BASE_DIR     = Path(__file__).parent.parent
DATASET_DIR  = BASE_DIR / "dataset" / "train"
CAPTURE_DIR  = Path(__file__).parent.parent.parent / "storage" / "app" / "public" / "captures"

cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

def has_face(img_path):
    img = cv2.imread(str(img_path))
    if img is None:
        return False
    gray  = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    faces = cascade.detectMultiScale(gray, scaleFactor=1.05, minNeighbors=4, minSize=(40,40))
    return len(faces) > 0

def list_dataset_folders():
    if not DATASET_DIR.exists():
        return []
    return sorted([d for d in DATASET_DIR.iterdir() if d.is_dir()])

def main():
    print("\n" + "="*55)
    print("  TAMBAH FOTO TRAINING LBPH")
    print("="*55)

    # Tampilkan folder dataset yang tersedia
    folders = list_dataset_folders()
    if not folders:
        print(f"\n[!] Belum ada folder dataset di: {DATASET_DIR}")
        print("Buat folder dengan format: Nama_ID (contoh: Peres_Hendri_Virgiawan_21)")
        return

    print(f"\n  Folder dataset tersedia ({len(folders)} kelas):")
    for i, f in enumerate(folders):
        n_photos = len(list(f.glob('*.jpg')) + list(f.glob('*.png')) + list(f.glob('*.jpeg')))
        print(f"  [{i:2d}] {f.name:<45} ({n_photos} foto)")

    # Pilih target orang
    print()
    try:
        choice = int(input("  Pilih nomor orang yang akan ditambah fotonya: ").strip())
        target_folder = folders[choice]
    except (ValueError, IndexError):
        print("[!] Pilihan tidak valid.")
        return

    print(f"\n  Target: {target_folder.name}")

    # Cari foto terbaru di captures
    if not CAPTURE_DIR.exists():
        print(f"\n[!] Folder captures tidak ditemukan: {CAPTURE_DIR}")
        return

    all_captures = sorted(CAPTURE_DIR.glob('web_*.jpg'), key=os.path.getmtime, reverse=True)
    if not all_captures:
        print(f"\n[!] Tidak ada foto di folder captures.")
        return

    # Filter yang punya wajah
    print(f"\n  Memeriksa {min(20, len(all_captures))} foto terbaru dari captures...")
    candidates = []
    for img_path in all_captures[:20]:
        if has_face(img_path):
            candidates.append(img_path)

    print(f"  {len(candidates)} foto mengandung wajah.\n")

    if not candidates:
        print("[!] Tidak ada foto dengan wajah terdeteksi di captures.")
        print("Coba absensi beberapa kali lagi lalu jalankan script ini.")
        return

    added = 0
    existing = len(list(target_folder.glob('*.jpg')))

    for img_path in candidates:
        # Hitung nomor urut
        new_num  = existing + added + 1
        new_name = target_folder / f"webcam_{new_num:04d}.jpg"

        # Salin file
        shutil.copy2(img_path, new_name)
        added += 1
        print(f"  + Ditambahkan: {new_name.name}")

        if added >= 10:  # Maksimal 10 foto baru per sesi
            break

    print(f"\n  {added} foto baru ditambahkan ke dataset {target_folder.name}")

    # Tawarkan retrain
    print()
    do_retrain = input("  Retrain model LBPH sekarang? [y/N]: ").strip().lower()
    if do_retrain == 'y':
        train_script = Path(__file__).parent / "train_lbph.py"
        if train_script.exists():
            print("\n  Menjalankan retrain...\n")
            os.system(f"{sys.executable} {train_script}")
        else:
            print(f"  [!] Script retrain tidak ditemukan: {train_script}")
            print("  Jalankan manual: python3 recognition_engine/scripts/train_lbph.py")
    else:
        print("\n  Untuk retrain manual, jalankan:")
        print("  python3 recognition_engine/scripts/train_lbph.py")
        print("  atau: php artisan face:retrain-lbph")
    print()

if __name__ == "__main__":
    main()
