# 🧠 Recognition Engine - Panti Asuhan Kasih Agape

Folder ini berisi semua aset dan script yang berkaitan dengan sistem Pengenalan Wajah (Face Recognition) menggunakan algoritma **LBPH** dan **CNN VGG16**.

## 📂 Struktur Direktori
- **`models/`**: Berisi file model yang sudah ditraining.
  - `lbph/`: `trainer.yml` dan `label_map.json`.
  - `vgg16/`: `best_model_vgg16.h5`, `embeddings.npy`, `label_encoder.pkl`.
- **`research/`**: Berisi aset untuk kebutuhan dokumentasi skripsi/laporan.
  - Plot Akurasi, Loss, Confusion Matrix, dan Classification Report.
- **`scripts/`**: (Rencana) Script Python untuk melakukan deteksi wajah secara real-time.

## 🛠️ Persiapan (Requirement)
Untuk menjalankan script di folder ini, Anda membutuhkan:
1. **Python 3.8+**
2. **OpenCV** (`pip install opencv-python`)
3. **TensorFlow/Keras** (Untuk VGG16)
4. **Scikit-learn** (Untuk Label Encoder)
5. **Requests** (Untuk mengirim data ke Laravel API)

## 📡 Alur Integrasi
1. Script di `scripts/` akan membaca stream CCTV.
2. Wajah akan dideteksi dan dikenali menggunakan model di `models/`.
3. Hasil pengenalan (ID Anak & Confidence) akan dikirim ke endpoint Laravel:
   `POST /api/face-recognition/store`

---
*Dibuat untuk integrasi sistem Panti Asuhan Kasih Agape.*
