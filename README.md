# 🏠 Panti Asuhan Kasih Agape

Sistem manajemen panti asuhan berbasis Laravel dengan fitur lengkap untuk admin, pengasuh, dan donatur.

## ✨ Fitur Utama

- **Dashboard Multi-Role**: Admin, Pengasuh, dan Donatur
- **Manajemen Kehadiran**: Sistem absensi anak-anak panti asuhan
- **Sistem CCTV**: Monitoring real-time untuk keamanan
- **Manajemen Profil Panti**: Informasi lengkap tentang panti asuhan
- **Galeri Foto**: Dokumentasi kegiatan dan acara
- **Sistem Autentikasi**: Role-based access control
- **Landing Page**: Website publik yang informatif

## 🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 10
- **Frontend**: Blade Templates, CSS3, JavaScript
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Assets**: Vite + Laravel Mix

## 📋 Requirements

- PHP 8.1+
- Composer
- Node.js & NPM
- MySQL/PostgreSQL
- Web Server (Apache/Nginx)

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/PeressHendri/panti-asuhan-kasih-agape.git
cd panti-asuhan-kasih-agape
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web_agape
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

### 6. Build Assets
```bash
npm run build
```

### 7. Start Development Server
```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## 👥 Role & Akses

### Admin
- Dashboard admin
- Manajemen pengguna
- Manajemen profil panti
- Sistem CCTV
- Laporan kehadiran

### Pengasuh
- Dashboard pengasuh
- Input kehadiran anak-anak
- Monitoring CCTV
- Update profil panti

### Donatur
- Dashboard donatur
- Lihat profil panti
- Monitoring CCTV
- Lihat laporan kehadiran

## 📁 Struktur Aplikasi

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Controller untuk admin
│   │   ├── Auth/           # Controller autentikasi
│   │   ├── Donatur/        # Controller untuk donatur
│   │   └── Pengasuh/       # Controller untuk pengasuh
│   └── Models/             # Model database
├── resources/views/
│   ├── admin/              # View untuk admin
│   ├── auth/               # View autentikasi
│   ├── donatur/            # View untuk donatur
│   ├── pengasuh/           # View untuk pengasuh
│   └── welcome.blade.php   # Landing page
├── public/assets/          # Assets statis (CSS, JS, Images)
└── routes/web.php          # Definisi route
```

## 🔧 Konfigurasi Tambahan

### Storage Link
Untuk mengakses file uploads:
```bash
php artisan storage:link
```

### Cache Configuration
Untuk production:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📸 Screenshots

### Landing Page
![Landing Page](public/assets/img/logoagape.png)

### Dashboard Admin
- Manajemen pengguna
- Sistem kehadiran
- Monitoring CCTV

## 🤝 Contributing

1. Fork repository ini
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 License

Project ini dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

## 📞 Kontak

**Panti Asuhan Kasih Agape**
- Alamat: Jl. Pakis Gunung I / 133 B, Surabaya, Jawa Timur
- WhatsApp: [0813-3130-7503](https://wa.me/6281331307503)
- Email: info@pantikasihagape.com

---

**"DIBERKATI UNTUK MENJADI BERKAT"** 🌟


