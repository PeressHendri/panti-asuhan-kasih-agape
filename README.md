# Panti Asuhan Kasih Agape 🏥

Selamat datang di sistem manajemen dan website resmi **Panti Asuhan Kasih Agape**. Proyek ini dirancang untuk profesionalitas tinggi, mengintegrasikan manajemen data panti dengan teknologi modern.

## 🚀 Fitur Utama

- **Website Publik Premium**: Desain modern dengan animasi halus (ScrollReveal, Typed.js), slider galeri (Swiper.js), dan layout yang sepenuhnya responsif.
- **Dashboard Multi-User**: Sistem login terpisah untuk **Admin**, **Pengasuh**, dan **Sponsor (Donatur)**.
- **Monitoring CCTV**: Integrasi pemantauan kamera IP secara langsung (Streaming HLS/RTSP) dari dashboard.
- **Face Recognition AI**: Log presensi otomatis berbasis pengenalan wajah (LBPH/CNN) yang terintegrasi dengan data kehadiran anak.
- **Galeri Multimedia**: Dukungan penuh untuk unggah foto dan **Video (.mp4, .webm)** dengan pemutar otomatis di landing page.
- **Sistem Donasi**: Form donasi online yang aman dengan fitur unggah bukti transfer.

## 🛠️ Panduan Admin & Pengasuh

### 1. Mengelola Galeri (Foto & Video)
- Navigasi ke menu **Galeri** di sidebar dashboard.
- Admin/Pengasuh dapat menambah konten baru. Sistem akan otomatis mendeteksi jika yang diunggah adalah video dan akan menampilkannya dengan video player di landing page.
- Batas unggah file: **5MB (Foto)** dan **20MB (Video)**.

### 2. Monitoring CCTV & Face Log
- Menu **Monitoring CCTV** menampilkan streaming kamera aktif.
- Menu **Face Recognition** menampilkan riwayat deteksi wajah secara real-time, termasuk peringatan jika ada wajah tidak dikenal (keamanan).

### 3. Manajemen Data Anak
- Kelola profil lengkap anak asuh yang akan terhubung dengan sistem presensi otomatis.

## 💻 Teknologi yang Digunakan

- **Backend**: Laravel 10 (PHP 8.1+)
- **Frontend**: HTML5, Vanilla CSS (Premium Custom Design), JavaScript
- **Libraries**:
  - `Swiper.js` (Slider Konten)
  - `Hls.js` (Streaming Video CCTV)
  - `Typed.js` & `ScrollReveal` (Animasi UI)
  - `FontAwesome 6` (Icons)
- **Database**: MySQL/PostgreSQL

## ⚙️ Instalasi & Setup

1. Clone repository.
2. Jalankan `composer install`.
3. Atur konfigurasi database di file `.env`.
4. Jalankan migrasi: `php artisan migrate --seed`.
5. **CRITICAL**: Jalankan `php artisan storage:link` agar media (foto/video) dapat tampil.
6. Akses aplikasi melalui `php artisan serve`.

## 📈 Statistik & SEO
- **Tahun Melayani**: Dihitung otomatis sejak panti berdiri tahun 2000.
- **Statistik Anak**: Ditampilkan secara statis (50+) sesuai kebutuhan desain.
- **SEO Optimized**: Sudah dilengkapi Meta Tags, OG Tags (untuk share medsos), dan Favicon resmi.

---
*Diberkati Untuk Menjadi Berkat.*
