# 🚀 Panduan Deployment & Distribusi Sistem Panti Agape

Dokumen ini menjelaskan langkah-langkah teknis untuk memindahkan sistem dari *Local Development* ke lingkungan *Production* (VPS) dan *Edge Device* (Raspberry Pi).

---

## 🏗️ 1. Deployment Web App (VPS - Ubuntu Server)
Web server bertugas sebagai pusat data (API) dan dashboard monitoring.

### Persyaratan Sistem:
- **OS:** Ubuntu 22.04 LTS (Rekomendasi)
- **Engine:** Nginx / Apache
- **Backend:** PHP 8.1+ & MySQL 8.0+ / MariaDB
- **Manager:** Composer & NPM

### Langkah Instalasi:
1. **Clone & Install:**
   ```bash
   git clone https://github.com/peress/panti-agape.git /var/www/panti-agape
   cd /var/www/panti-agape
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```

2. **Konfigurasi Environment (.env):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   **PENTING:** Sesuaikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` dengan kredensial VPS Anda. Ubah `APP_ENV=production` dan `APP_DEBUG=false`.

3. **Database & Storage:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   ```

4. **Izin Folder:**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   ```

---

## 🤖 2. Deployment Engine AI (Raspberry Pi / Laptop Lokal)
Edge device bertugas memproses stream video dan mengirim hasil deteksi ke VPS.

### Persyaratan Sistem:
- **Hardware:** Raspberry Pi 4 (8GB RAM disarankan) / Mini PC.
- **Python:** Versi 3.8+
- **Camera:** Modul Pi Cam / IP CCTV (Wireless/Wired).

### Langkah Instalasi:
1. **Transfer Folder:**
   Copy folder `recognition_engine` ke `/home/pi/recognition_engine`.

2. **Install Library Utama:**
   Update sistem dan install library OpenCV versi kontributor (mendukung LBPH).
   ```bash
   sudo apt update && sudo apt upgrade
   pip install opencv-contrib-python requests pymysql
   ```

3. **Sinkronisasi Identitas (PENTING):**
   Raspberry Pi harus tahu ID anak yang ada di database VPS agar tidak salah kirim absensi.
   - Buka `sync_label_map.py`.
   - Ubah `DB_CONFIG` (host gunakan IP VPS atau Domain VPS Anda).
   - Jalankan:
     ```bash
     python3 sync_label_map.py
     ```

4. **Konfigurasi API & Token:**
   - Buka `main_recognition.py`.
   - Ubah `API_BASE_URL` ke domain VPS Anda (contoh: `https://pantiagape.com/api`).
   - Pastikan `API_TOKEN` sama dengan yang ada di `.env` VPS.

5. **Jalankan Background Process:**
   Gunakan `screen` atau `systemd` agar program tetap jalan saat terminal ditutup.
   ```bash
   python3 main_recognition.py
   ```

---

## 🎥 3. Integrasi IP CCTV
Sistem ini menggunakan protokol **RTSP** (*Real-Time Streaming Protocol*) untuk mengambil data video.

### Format URL CCTV:
Tambahkan kamera di Dashboard Admin dengan format URL sesuai merk CCTV Anda:
- **Hikvision/Dahua:** `rtsp://admin:password@192.168.1.100:554/Streaming/Channels/101`
- **TP-Link Tapo:** `rtsp://admin:password@192.168.1.50:554/stream1`
- **Webcam Lokal (Testing):** Gunakan angka `0`.

### Tips Troubleshooting:
1. **Lag Video:** Jika VPS dan Kamera berada di jaringan yang sama (Lokal), gunakan IP lokal untuk akses cepat.
2. **Confidence Level:** Jika sistem "terlalu ketat" dalam mengenali wajah (menganggap semua orang asing), turunkan **Confidence Threshold** di menu **Profil Admin** (Web) menjadi kisaran **65-70**. Default adalah **75**.
3. **Pencahayaan:** Pastikan posisi kamera tidak membelakangi cahaya (backlight) agar fitur *Face Recognition* bekerja optimal.

---

## 🏁 Kesimpulan Alur Kerja
1. User (Donatur/Admin) akses **VPS** via Browser.
2. **Raspberry Pi** mengambil stream video dari **CCTV IP**.
3. **Raspberry Pi** kirim data deteksi ke **VPS** tiap detik.
4. Dashboard **VPS** update otomatis menampilkan kehadiran & aktivitas anak.
