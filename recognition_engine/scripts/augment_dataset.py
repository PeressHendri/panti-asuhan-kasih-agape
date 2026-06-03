import cv2
import numpy as np
import os
import glob
from pathlib import Path

def augment_face(img):
    """
    Dari 1 foto, generate ~7 variasi.
    Total: 50 foto × 7 = ~350 foto per anak.
    """
    results = [img]  # foto asli tetap disimpan

    h, w = img.shape[:2]

    # 1. Flip horizontal (cermin) — simulasi wajah menghadap sedikit ke kiri/kanan
    results.append(cv2.flip(img, 1))

    # 2. Kecerahan lebih terang
    bright = cv2.convertScaleAbs(img, alpha=1.3, beta=30)
    results.append(bright)

    # 3. Kecerahan lebih gelap — simulasi ruangan redup
    dark = cv2.convertScaleAbs(img, alpha=0.7, beta=-20)
    results.append(dark)

    # 4. Slight rotation kiri (-10°) — simulasi kepala miring
    M_left = cv2.getRotationMatrix2D((w//2, h//2), 10, 1.0)
    results.append(cv2.warpAffine(img, M_left, (w, h)))

    # 5. Slight rotation kanan (+10°)
    M_right = cv2.getRotationMatrix2D((w//2, h//2), -10, 1.0)
    results.append(cv2.warpAffine(img, M_right, (w, h)))

    # 6. Blur ringan — simulasi kamera kurang fokus / gerak
    results.append(cv2.GaussianBlur(img, (3, 3), 0))

    # 7. CLAHE — simulasi perbaikan kontras otomatis kamera
    # Penanganan khusus jika gambar grayscale (1-channel) atau BGR (3-channel) agar tidak crash
    if len(img.shape) == 3 and img.shape[2] == 3:
        lab = cv2.cvtColor(img, cv2.COLOR_BGR2LAB)
        l, a, b = cv2.split(lab)
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8,8))
        lab_eq = cv2.merge([clahe.apply(l), a, b])
        results.append(cv2.cvtColor(lab_eq, cv2.COLOR_LAB2BGR))
    else:
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8,8))
        results.append(clahe.apply(img))

    return results


def augment_dataset(dataset_dir):
    """
    Struktur folder yang diharapkan:
    dataset/
      ├── child_001/
      │     ├── foto1.jpg
      │     ├── foto2.jpg
      │     └── ...
      ├── child_002/
      └── ...
    """
    child_dirs = [d for d in Path(dataset_dir).iterdir() if d.is_dir()]

    if not child_dirs:
        print("❌ Tidak ada subfolder anak ditemukan di:", dataset_dir)
        return

    for child_dir in sorted(child_dirs):
        foto_list = list(child_dir.glob("*.jpg")) + \
                    list(child_dir.glob("*.jpeg")) + \
                    list(child_dir.glob("*.png"))

        # Skip file yang sudah hasil augmentasi sebelumnya
        foto_list = [f for f in foto_list if "_aug" not in f.stem]

        if not foto_list:
            print(f"⚠️  Tidak ada foto di {child_dir.name}, skip.")
            continue

        aug_count = 0
        for foto_path in foto_list:
            img = cv2.imread(str(foto_path))
            if img is None:
                continue

            variasi = augment_face(img)

            # Simpan variasi (skip index 0 karena itu foto asli)
            for i, aug_img in enumerate(variasi[1:], start=1):
                save_name = f"{foto_path.stem}_aug{i}{foto_path.suffix}"
                save_path = child_dir / save_name
                cv2.imwrite(str(save_path), aug_img)
                aug_count += 1

        total = len(foto_list) + aug_count
        print(f"✅ {child_dir.name}: {len(foto_list)} asli + {aug_count} augmentasi = {total} foto")

    print("\n🎉 Augmentasi selesai! Silakan retrain model Anda.")


if __name__ == "__main__":
    import sys
    if len(sys.argv) < 2:
        print("Usage: python augment_dataset.py /path/ke/dataset")
        sys.exit(1)

    augment_dataset(sys.argv[1])
