<div align="center">

# 🏠 Sistem Monitoring Panti Asuhan Kasih Agape
### AI-Powered Face Recognition & CCTV Monitoring System

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.11-3776AB?style=for-the-badge&logo=python&logoColor=white)
![OpenCV](https://img.shields.io/badge/OpenCV-4.8-5C3EE8?style=for-the-badge&logo=opencv&logoColor=white)
![Raspberry Pi](https://img.shields.io/badge/Raspberry%20Pi-4B-C51A4A?style=for-the-badge&logo=raspberry-pi&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

</div>

---

## 📋 Daftar Isi

1. [Gambaran Sistem](#-gambaran-sistem)
2. [Topologi Sistem](#-topologi-sistem)
3. [Spesifikasi Teknologi](#-spesifikasi-teknologi)
4. [Spesifikasi Hardware](#-spesifikasi-hardware)
5. [Spesifikasi VPS](#-spesifikasi-vps)
6. [Wiring Diagram](#-wiring-diagram-raspberry-pi)
7. [Deploy Laravel ke VPS](#-deploy-laravel-ke-vps)
8. [Setup Raspberry Pi](#-setup-raspberry-pi)
9. [Konfigurasi CCTV](#-konfigurasi-cctv)
10. [Training Model LBPH](#-training-model-lbph)
11. [Variabel Lingkungan](#-variabel-lingkungan)
12. [Struktur Database](#-struktur-database)
13. [API Endpoints](#-api-endpoints)
14. [Checklist Verifikasi](#-checklist-verifikasi)

---

## 🌟 Gambaran Sistem

Sistem monitoring terintegrasi untuk **Panti Asuhan Kasih Agape** yang menggabungkan:

| Fitur | Teknologi | Keterangan |
|---|---|---|
| **Presensi Otomatis** | OpenCV LBPH + Haar Cascade | Face recognition via webcam pintu |
| **Pemicu Cerdas** | Sensor PIR HC-SR501 | Kamera hanya aktif saat ada orang |
| **Feedback Lokal** | Buzzer + LCD 16x2 I2C | Output langsung di Raspberry Pi |
| **CCTV Monitoring** | MOG2 Background Subtraction | Analisis intensitas aktivitas ruangan |
| **NVR Real-Time** | Flask MJPEG + Dual-Thread | Streaming video tanpa delay |
| **Dashboard Web** | Laravel + AJAX Polling | Auto-update tanpa reload halaman |
| **Manajemen Donasi** | Laravel | Verifikasi bukti transfer |
| **Multi-Role** | Laravel Auth | Admin, Pengasuh, Sponsor/Donatur |

---

## 🗺️ Topologi Sistem

```
┌─────────────────────────────────────────────────────────┐
│                PERANGKAT EDGE (Raspberry Pi 4B)         │
│                                                         │
│  ┌─────────┐    GPIO17    ┌──────────────────────────┐  │
│  │Sensor   │─────────────►│                          │  │
│  │PIR      │  (HIGH=Ada   │   main_recognition.py    │  │
│  │HC-SR501 │   Orang)     │                          │  │
│  └─────────┘              │  Thread 1: Capture ~30fps │  │
│                           │  Thread 2: AI Analysis   │  │
│  ┌─────────┐    USB       │                          │  │
│  │Webcam   │─────────────►│  [WEBCAM PINTU]          │  │
│  │USB      │              │  PIR HIGH → Haar Cascade │  │
│  └─────────┘              │          → LBPH Predict  │  │
│                           │          → POST API      │  │
│  ┌─────────┐   GPIO27     │                          │  │
│  │Active   │◄─────────────│  [CCTV RUANG BERSAMA]   │  │
│  │Buzzer   │  (beep)      │  MOG2 → Klasifikasi      │  │
│  └─────────┘              │  Kosong/Pasif/Aktif/Aktif│  │
│                           │          → POST API      │  │
│  ┌─────────┐   I2C(0x27) │                          │  │
│  │LCD 16x2 │◄─────────────│  Flask MJPEG Server      │  │
│  │I2C      │  (nama/alert)│  Port: 5000             │  │
│  └─────────┘              └──────────────────────────┘  │
│                                    │         ▲           │
│  ┌─────────┐   RTSP/Wi-Fi         │         │           │
│  │Smart    │──────────────────────┘         │ RTSP      │
│  │CCTV IP  │                               │ Stream    │
│  │Camera   │                               │           │
│  └─────────┘                               │           │
└───────────────────────────────────────────-┼───────────┘
                         │                   │
                    POST JSON            (Pi pull RTSP
                  /api/face-recognition    dari CCTV)
                  /api/cctv/activity
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                    VPS CLOUD SERVER                     │
│                                                         │
│  Nginx ──► Laravel 10 (PHP 8.1)                        │
│               │                                         │
│               ├── Auth (Admin/Pengasuh/Donatur)         │
│               ├── API: face-recognition, cctv           │
│               ├── Dashboard CCTV (AJAX Polling 10s)     │
│               ├── Manajemen Kehadiran & Donasi          │
│               └── MySQL 8.0                            │
│                                                         │
└─────────────────────────────────────────────────────────┘
                         │
                         ▼ MJPEG Stream
                    Browser Admin
                  (http://PI-IP:5000/
                   video_feed/KAMERA_ID)
```

---

## 🛠️ Spesifikasi Teknologi

### Backend (Laravel)
| Komponen | Versi | Fungsi |
|---|---|---|
| **PHP** | 8.1+ | Runtime Laravel |
| **Laravel** | 10.x | Framework web |
| **MySQL** | 8.0 | Database utama |
| **Laravel Sanctum** | 3.2 | Token autentikasi API |
| **Predis** | 3.x | Redis client (opsional) |
| **Guzzle HTTP** | 7.x | HTTP client |

### AI & Computer Vision (Python)
| Library | Versi | Fungsi |
|---|---|---|
| **OpenCV** | 4.8.x | Computer vision utama |
| **opencv-contrib** | 4.8.x | LBPH Face Recognizer |
| **Flask** | 3.x | MJPEG stream server |
| **NumPy** | 1.24+ | Array processing |
| **Requests** | 2.31+ | HTTP POST ke Laravel API |
| **RPi.GPIO** | 0.7+ | GPIO: PIR + Buzzer |
| **RPLCD** | 1.3+ | LCD 16x2 I2C display |
| **smbus2** | 0.4+ | I2C bus communication |
| **Ultralytics** | 8.x | YOLOv8 (activity tracker) |

### Frontend
| Komponen | Keterangan |
|---|---|
| **Blade** | Template engine Laravel |
| **Bootstrap 5** | CSS framework |
| **Font Awesome 6** | Icon library |
| **hls.js** | HLS video player (CCTV) |
| **Vanilla JS AJAX** | Polling live data tanpa library |

---

## 💻 Spesifikasi Hardware

### Raspberry Pi (Terkonfirmasi)
```
Model   : Raspberry Pi 4 Model B Rev 1.5
CPU     : ARM Cortex-A72 (ARMv8) 64-bit @ 1.800GHz (4 core)
RAM     : 8GB LPDDR4-3200 SDRAM
Storage : 16GB MicroSD Class 10 (minimal)
OS      : Debian GNU/Linux 12 (Bookworm) aarch64
Kernel  : 6.1.0-rpi7-rpi-v8
Network : Gigabit Ethernet + Wi-Fi 802.11ac
USB     : 2x USB 3.0 + 2x USB 2.0
GPIO    : 40-pin header
```

### Komponen Tambahan
| Komponen | Model | Spesifikasi |
|---|---|---|
| **Sensor PIR** | HC-SR501 | 5V, sudut 120°, jarak 3-7m, delay adj. |
| **Buzzer** | Active Buzzer 5V | 5V, ~85dB, frekuensi 2.5kHz |
| **LCD** | 16x2 I2C (PCF8574) | 5V, address 0x27 atau 0x3F |
| **Webcam** | USB Webcam (min 720p) | USB 2.0/3.0, 30fps, autofocus |
| **Smart CCTV** | IP Camera | RTSP support, Wi-Fi/LAN, min 1080p |
| **Power Supply** | Official RPi 5V/3A | USB-C, output stabil |

### Kebutuhan Jaringan
| Kebutuhan | Keterangan |
|---|---|
| **LAN/Wi-Fi** | Pi dan CCTV harus satu subnet |
| **Internet** | Pi harus bisa reach VPS (HTTPS) |
| **IP Pi** | Statis/DHCP reservation di router |
| **Port 5000** | Untuk MJPEG stream Flask (LAN only) |

---

## 🖥️ Spesifikasi VPS

### Minimum yang Direkomendasikan
```
CPU     : 2 vCore (x86_64)
RAM     : 2 GB DDR4
Storage : 40 GB SSD NVMe
Network : 100 Mbps Unmetered / 2TB bulan
OS      : Ubuntu 22.04 LTS
```

### Software Stack VPS
```
Nginx       1.18+   (Web Server)
PHP-FPM     8.1+    (Laravel runtime)
MySQL       8.0+    (Database)
Certbot             (SSL/HTTPS gratis)
```

### Rekomendasi Provider (Budget-Friendly)
| Provider | RAM | Storage | Harga/bln | Link |
|---|---|---|---|---|
| **Niagahoster VPS Cloud 2** | 2GB | 40GB SSD | ~Rp 60rb | niagahoster.co.id |
| **IDCloudHost** | 2GB | 40GB SSD | ~Rp 75rb | idcloudhost.com |
| **Dewaweb** | 2GB | 30GB SSD | ~Rp 85rb | dewaweb.com |
| **Contabo VPS S** | 4GB | 100GB SSD | ~€4.99 | contabo.com |
| **DigitalOcean Droplet** | 2GB | 50GB SSD | ~$12 | digitalocean.com |

> **Catatan:** Untuk keperluan skripsi/demo, Niagahoster atau Contabo adalah pilihan paling ekonomis.

---

## 🔌 Wiring Diagram Raspberry Pi

```
Raspberry Pi 4B — 40-pin GPIO Header
════════════════════════════════════════

  RPi Pin  │ GPIO BCM │ Terhubung ke
  ─────────┼──────────┼──────────────────────────────
  Pin  2   │   5V     │ PIR VCC  (+)
  Pin  2   │   5V     │ Buzzer (+) [active buzzer 5V]
  Pin  2   │   5V     │ LCD VCC
  Pin  3   │ GPIO 2   │ LCD SDA  (I2C Data)
  Pin  5   │ GPIO 3   │ LCD SCL  (I2C Clock)
  Pin  6   │   GND    │ PIR GND  (-)
  Pin  6   │   GND    │ Buzzer (-) / GND
  Pin  6   │   GND    │ LCD GND
  Pin 11   │ GPIO 17  │ PIR OUT  → [INPUT]
  Pin 13   │ GPIO 27  │ Buzzer + → [OUTPUT]
  USB Port │    -     │ Webcam USB
  Wi-Fi    │    -     │ Smart CCTV via RTSP (LAN)

Keterangan:
  [INPUT]  = GPIO.setup(17, GPIO.IN)
  [OUTPUT] = GPIO.setup(27, GPIO.OUT)

LCD I2C Address: 0x27 (default PCF8574)
  Jika tidak muncul, coba: 0x3F
  Cek dengan: sudo i2cdetect -y 1
```

---

## 🚀 Deploy Laravel ke VPS

### Langkah 1 — Persiapan VPS

```bash
# SSH ke VPS
ssh root@IP-VPS-ANDA

# Update sistem
sudo apt update && sudo apt upgrade -y

# Install dependency
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
  php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml \
  php8.1-bcmath php8.1-curl php8.1-zip php8.1-gd \
  php8.1-intl php8.1-tokenizer \
  nginx mysql-server git unzip curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### Langkah 2 — Setup Database MySQL

```bash
sudo mysql_secure_installation
sudo mysql -u root -p
```

```sql
CREATE DATABASE kasih_agape
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'kasih_user'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT_ANDA';
GRANT ALL PRIVILEGES ON kasih_agape.* TO 'kasih_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Langkah 3 — Upload & Setup Laravel

```bash
# Buat user untuk aplikasi
sudo adduser deploy
sudo usermod -aG www-data deploy

# Clone repo ke VPS
cd /var/www
sudo git clone https://github.com/USERNAME/REPO.git kasih-agape
sudo chown -R deploy:www-data /var/www/kasih-agape
cd /var/www/kasih-agape

# Install PHP dependencies
sudo -u deploy composer install --no-dev --optimize-autoloader

# Setup environment
sudo -u deploy cp .env.example .env
sudo nano .env
```

### Langkah 4 — Konfigurasi .env Produksi

```env
APP_NAME="Panti Asuhan Kasih Agape"
APP_ENV=production
APP_KEY=                          # Di-generate otomatis
APP_DEBUG=false
APP_URL=https://domain-anda.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasih_agape
DB_USERNAME=kasih_user
DB_PASSWORD=PASSWORD_KUAT_ANDA

FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file

# Token autentikasi Raspberry Pi (HARUS SAMA dengan di Python)
RASPBERRY_PI_TOKEN=kasihagape2025secret

# IP dan Port Flask stream (Raspberry Pi)
RASPBERRY_PI_IP=192.168.1.50
RASPBERRY_PI_STREAM_PORT=5000
```

### Langkah 5 — Finalisasi Laravel

```bash
# Jalankan sebagai user deploy
cd /var/www/kasih-agape

php artisan key:generate
php artisan migrate --force
php artisan db:seed --force    # Jika ada seeder
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Permission
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 755 public
```

### Langkah 6 — Konfigurasi Nginx

```bash
sudo nano /etc/nginx/sites-available/kasih-agape
```

```nginx
server {
    listen 80;
    server_name domain-anda.com www.domain-anda.com;
    root /var/www/kasih-agape/public;
    index index.php index.html;

    # Nonaktifkan buffer (penting untuk MJPEG stream via proxy)
    proxy_buffering off;
    
    # Ukuran upload (untuk foto bukti donasi)
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass  unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_read_timeout 300;
        include fastcgi_params;
    }

    location ~* \.(css|js|gif|jpg|jpeg|png|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    location ~ /\. {
        deny all;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
}
```

```bash
# Aktifkan site
sudo ln -s /etc/nginx/sites-available/kasih-agape /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Langkah 7 — SSL/HTTPS Gratis

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d domain-anda.com -d www.domain-anda.com

# Auto-renewal (otomatis)
sudo crontab -e
# Tambahkan baris ini:
0 3 * * * certbot renew --quiet && systemctl reload nginx
```

### Langkah 8 — PHP-FPM Konfigurasi (Opsional, untuk performa)

```bash
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

```ini
; Ubah user/group
user = deploy
group = www-data

; Untuk 2GB RAM:
pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 10
```

```bash
sudo systemctl restart php8.1-fpm
```

---

## 🍓 Setup Raspberry Pi

### Langkah 1 — Persiapan OS

```bash
# Update sistem (di Raspberry Pi)
sudo apt update && sudo apt upgrade -y

# Install dependency sistem
sudo apt install -y \
  python3-pip python3-venv \
  libopencv-dev python3-opencv \
  python3-smbus i2c-tools \
  libatlas-base-dev \
  libjpeg-dev libpng-dev \
  git build-essential

# Aktifkan interface I2C (untuk LCD)
sudo raspi-config
# Pilih: 3 Interface Options
#        → I3 I2C
#        → Yes (Enable)
#        → Finish
#        → Reboot? Yes

sudo reboot
```

### Langkah 2 — Verifikasi Hardware

```bash
# Cek LCD I2C (harus muncul address 0x27 atau 0x3F)
sudo i2cdetect -y 1

# Output yang diharapkan:
#      0  1  2  3  4  5  6  7  8  9  a  b  c  d  e  f
# 20:                            27                   

# Test PIR (pindahkan tangan di depan sensor)
python3 -c "
import RPi.GPIO as GPIO, time
GPIO.setmode(GPIO.BCM)
GPIO.setup(17, GPIO.IN)
print('Monitoring PIR... (Ctrl+C untuk stop)')
try:
    while True:
        print('PIR State:', 'DETECTED' if GPIO.input(17) else 'IDLE')
        time.sleep(0.5)
except KeyboardInterrupt:
    GPIO.cleanup()
"

# Test Buzzer
python3 -c "
import RPi.GPIO as GPIO, time
GPIO.setmode(GPIO.BCM)
GPIO.setup(27, GPIO.OUT)
print('Buzzer ON...')
GPIO.output(27, GPIO.HIGH); time.sleep(0.5)
GPIO.output(27, GPIO.LOW)
print('Buzzer OFF - OK')
GPIO.cleanup()
"

# Test Webcam
python3 -c "
import cv2
cap = cv2.VideoCapture(0)
print('Webcam OK:', cap.isOpened())
ret, frame = cap.read()
print('Frame shape:', frame.shape if ret else 'GAGAL')
cap.release()
"
```

### Langkah 3 — Buat Virtual Environment Python

```bash
cd ~
python3 -m venv venv-panti
source venv-panti/bin/activate

# Upgrade pip
pip install --upgrade pip setuptools wheel

# Install semua library
pip install \
  opencv-contrib-python-headless==4.8.1.78 \
  flask==3.0.3 \
  requests==2.31.0 \
  numpy==1.26.4 \
  RPi.GPIO==0.7.1 \
  RPLCD==1.3.0 \
  smbus2==0.4.3

# Verifikasi install
python3 -c "import cv2; print('OpenCV:', cv2.__version__)"
python3 -c "import flask; print('Flask:', flask.__version__)"
python3 -c "import RPi.GPIO as GPIO; print('GPIO OK')"
python3 -c "from RPLCD.i2c import CharLCD; print('RPLCD OK')"
```

### Langkah 4 — Upload Kode ke Raspberry Pi

```bash
# Opsi A — Via SCP dari laptop
scp -r /path/ke/recognition_engine/ azza@IP-RASPBERRY:/home/azza/panti-recognition/

# Opsi B — Via Git
git clone https://github.com/USERNAME/REPO.git ~/panti-recognition
```

### Langkah 5 — Konfigurasi main_recognition.py

```bash
nano ~/panti-recognition/recognition_engine/main_recognition.py
```

Ubah bagian konfigurasi:

```python
# ==========================================
# KONFIGURASI API SERVER LARAVEL
# ==========================================
API_BASE_URL = "https://domain-anda.com/api"   # ← URL VPS Anda
API_TOKEN    = "kasihagape2025secret"           # ← Harus sama dengan .env VPS

# ==========================================
# KONFIGURASI GPIO
# ==========================================
PIR_PIN    = 17   # BCM GPIO 17 — Sensor PIR
BUZZER_PIN = 27   # BCM GPIO 27 — Active Buzzer

# LCD I2C Address (cek: sudo i2cdetect -y 1)
# address=0x27  ← Ubah ke 0x3F jika LCD tidak terdeteksi
```

### Langkah 6 — Test Run Manual

```bash
source ~/venv-panti/bin/activate
cd ~/panti-recognition/recognition_engine
python3 main_recognition.py
```

**Output sukses yang diharapkan:**
```
✅ PIR GPIO17 | Buzzer GPIO27 aktif.
✅ LCD 16x2 I2C aktif di address 0x27.
✅ Confidence Threshold dari Web: 75
===================================================
 👁️  PANTI AGAPE AI WATCHER DAEMON (AUTO-DETECTOR)
===================================================
🚀 Web API Video aktif di port 5000
CCTV Anda sekarang bisa dilihat dari Web Laravel!
[01:30:00] Mencari kamera aktif dari Server...
```
```
LCD menampilkan:
┌────────────────┐
│ Sistem Aktif   │
│ Panti KasihAgap│
└────────────────┘
```

### Langkah 7 — Setup Systemd Service (Auto-start)

```bash
sudo nano /etc/systemd/system/panti-ai.service
```

```ini
[Unit]
Description=Panti Asuhan Kasih Agape - AI Recognition Engine
Documentation=https://github.com/USERNAME/REPO
After=network-online.target multi-user.target
Wants=network-online.target

[Service]
Type=simple
User=azza
Group=azza
WorkingDirectory=/home/azza/panti-recognition/recognition_engine
ExecStart=/home/azza/venv-panti/bin/python3 main_recognition.py
ExecReload=/bin/kill -HUP $MAINPID
Restart=always
RestartSec=10
TimeoutStartSec=30
TimeoutStopSec=10

# Log output
StandardOutput=journal
StandardError=journal
SyslogIdentifier=panti-ai

# Variabel environment
Environment=PYTHONUNBUFFERED=1
Environment=PYTHONDONTWRITEBYTECODE=1

[Install]
WantedBy=multi-user.target
```

```bash
# Aktifkan service
sudo systemctl daemon-reload
sudo systemctl enable panti-ai.service
sudo systemctl start panti-ai.service

# Periksa status
sudo systemctl status panti-ai.service

# Lihat log real-time
sudo journalctl -u panti-ai.service -f

# Perintah berguna
sudo systemctl restart panti-ai   # Restart service
sudo systemctl stop panti-ai      # Stop service
sudo journalctl -u panti-ai -n 100  # 100 log terakhir
```

---

## 📷 Konfigurasi CCTV

Setelah Laravel dan Raspberry Pi berjalan, daftarkan kamera melalui dashboard:

**Login → Dashboard → CCTV → Klik tombol `+`**

### Webcam USB (Pintu Masuk — Raspberry Pi)

| Field | Nilai |
|---|---|
| **Kamera ID** | `webcam_pintu` ⚠️ Wajib mengandung kata `webcam` atau `pintu` |
| **Nama** | `Webcam Pintu Utama` |
| **RTSP URL** | `0` (angka nol = webcam USB lokal Pi) |
| **HLS URL** | `http://192.168.1.50:5000/video_feed/webcam_pintu` |
| **Lokasi** | `Pintu Masuk` |
| **Aktif** | ✅ |

### Smart CCTV IP Camera (Ruang Bersama)

| Field | Nilai |
|---|---|
| **Kamera ID** | `ruang_bersama` (nama bebas, bukan `webcam`/`pintu`) |
| **Nama** | `CCTV Ruang Bersama` |
| **RTSP URL** | `rtsp://admin:password@192.168.1.100:554/stream1` |
| **HLS URL** | `http://192.168.1.50:5000/video_feed/ruang_bersama` |
| **Lokasi** | `Ruang Bersama` |
| **Aktif** | ✅ |

### Format RTSP URL per Merk CCTV

| Merk | Format RTSP |
|---|---|
| **Hikvision** | `rtsp://admin:password@IP:554/Streaming/Channels/101` |
| **Dahua** | `rtsp://admin:password@IP:554/cam/realmonitor?channel=1&subtype=0` |
| **Reolink** | `rtsp://admin:password@IP:554/h264Preview_01_main` |
| **Imou** | `rtsp://admin:password@IP:554/cam/realmonitor?channel=1` |
| **Generic/Murah** | `rtsp://admin:password@IP:554/stream1` |
| **Tanpa password** | `rtsp://IP:554/stream1` |

> **Logika kamera_id:**
> - Mengandung **`webcam`** atau **`pintu`** → **Mode PIR Hardware** (Face Recognition)
> - Nama lain → **Mode MOG2 Software** (Activity Monitoring)

---

## 🧠 Training Model LBPH

Model LBPH harus ditraining sebelum sistem face recognition bisa bekerja.

### Langkah 1 — Ambil Dataset Foto

```bash
source ~/venv-panti/bin/activate
cd ~/panti-recognition/recognition_engine

# Jalankan script pengambilan foto
# Pastikan child_id sesuai dengan ID anak di database
python3 scripts/capture_dataset.py
```

**Struktur folder dataset:**
```
recognition_engine/
└── datasets/
    ├── 1/          ← child_id = 1 (ambil min. 20 foto)
    │   ├── 001.jpg
    │   ├── 002.jpg
    │   └── ...
    ├── 2/          ← child_id = 2
    └── ...
```

### Langkah 2 — Sinkronisasi Label Map dengan Database

```bash
# Sync label_map agar child_id di Python sesuai dengan database Laravel
python3 sync_label_map.py

# Hasilnya:
# models/lbph/label_map_synced.json
```

### Langkah 3 — Training Model

```bash
# Training LBPH dari dataset
python3 scripts/train_lbph.py

# Hasilnya:
# models/lbph/trainer.yml   ← Model terlatih
# models/lbph/label_map.json
```

### Langkah 4 — Verifikasi Model

```bash
# Test recognition
python3 scripts/recognize_lbph.py

# Threshold confidence (dapat diubah dari dashboard admin)
# Default: 75 (semakin rendah = semakin ketat)
```

---

## ⚙️ Variabel Lingkungan

### Laravel `.env` (VPS)

```env
# Aplikasi
APP_NAME="Panti Asuhan Kasih Agape"
APP_ENV=production
APP_KEY=base64:GENERATED_KEY
APP_DEBUG=false
APP_URL=https://domain-anda.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasih_agape
DB_USERNAME=kasih_user
DB_PASSWORD=password_kuat_anda

# Filesystem
FILESYSTEM_DISK=public

# Cache & Session
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Raspberry Pi Integration
RASPBERRY_PI_TOKEN=kasihagape2025secret    # HARUS SAMA dengan API_TOKEN di Python
RASPBERRY_PI_IP=192.168.1.50              # IP Raspberry Pi di jaringan lokal
RASPBERRY_PI_STREAM_PORT=5000             # Port Flask MJPEG server
```

### Konfigurasi Python (`main_recognition.py`)

```python
API_BASE_URL = "https://domain-anda.com/api"   # URL API Laravel
API_TOKEN    = "kasihagape2025secret"           # HARUS SAMA dengan RASPBERRY_PI_TOKEN

PIR_PIN      = 17   # GPIO BCM Sensor PIR
BUZZER_PIN   = 27   # GPIO BCM Active Buzzer
# LCD I2C address = 0x27 (atau 0x3F)

CONFIDENCE_THRESHOLD = 75  # Diambil otomatis dari Laravel admin settings
MOTION_AREA_THRESHOLD = 3000  # px² untuk MOG2 trigger
```

---

## 🗄️ Struktur Database

### Tabel Utama

```
users                    ← Admin, Pengasuh, Donatur
children                 ← Data anak panti (nama, foto, dll)
face_recognition_logs    ← Log presensi wajah dari Pi
cctv_cameras             ← Daftar kamera terdaftar
cctv_activity_logs       ← Log aktivitas dari MOG2/YOLO
attendances              ← Rekap kehadiran harian
donasi                   ← Data donasi dari donatur
profile_pantis           ← Info & galeri panti
galleries                ← Foto kegiatan panti
```

### Kolom Penting face_recognition_logs

```sql
id                UUID    PRIMARY KEY
child_id          INT     FK → children.id (NULL jika tidak dikenal)
kamera_id         VARCHAR FK → cctv_cameras.kamera_id
confidence_score  DECIMAL Skor kepercayaan LBPH (0-100)
algoritma         ENUM    'lbph', 'haar'
status            ENUM    'check_in', 'tidak_dikenal'
foto_capture_path VARCHAR Path foto tangkapan (storage)
waktu_deteksi     DATETIME Timestamp deteksi
```

---

## 📡 API Endpoints

### Endpoint dari Raspberry Pi ke Laravel

| Method | Endpoint | Auth | Fungsi |
|---|---|---|---|
| POST | `/api/face-recognition` | Bearer Token | Kirim data wajah terdeteksi |
| POST | `/api/cctv/activity` | Bearer Token | Kirim log aktivitas MOG2 |
| POST | `/api/cctv/status` | Bearer Token | Update status online kamera |
| POST | `/api/cctv/yolo-log` | Bearer Token | Kirim data YOLO tracker |
| GET | `/api/cctv/cameras` | Bearer Token | Ambil daftar kamera aktif |
| GET | `/api/children/for-training` | Bearer Token | Sync data anak untuk training |

### Autentikasi API

Semua request dari Raspberry Pi **wajib** menyertakan header:
```
Authorization: Bearer kasihagape2025secret
Content-Type: application/json
Accept: application/json
```

### Contoh Payload Face Recognition

```json
POST /api/face-recognition
{
  "child_id": 5,
  "confidence_score": 87.3,
  "algoritma": "lbph",
  "status": "check_in",
  "kamera_id": "webcam_pintu",
  "foto_base64": "data:image/jpeg;base64,/9j/4AAQ..."
}
```

---

## ✅ Checklist Verifikasi

### VPS
```
[ ] Website dapat diakses: https://domain-anda.com
[ ] Login admin berhasil
[ ] php artisan migrate berhasil (semua tabel ada)
[ ] storage:link berjalan (foto bisa ditampilkan)
[ ] SSL aktif (gembok hijau di browser)
[ ] Nginx tidak error (sudo nginx -t)
```

### Raspberry Pi
```
[ ] sudo systemctl status panti-ai → Active (running) ✓
[ ] sudo i2cdetect -y 1 → muncul address 0x27 atau 0x3F
[ ] LCD menampilkan "Sistem Aktif"
[ ] Gerakkan tangan di depan PIR → LED PIR menyala
[ ] Test buzzer → bunyi keluar
[ ] python3 -c "import cv2; cap=cv2.VideoCapture(0); print(cap.isOpened())"
    → True
```

### Integrasi Full System
```
[ ] Daftarkan webcam_pintu (RTSP: 0) di dashboard
[ ] Daftarkan ruang_bersama (RTSP: rtsp://...) di dashboard
[ ] Tunggu 15 detik → badge kamera berubah ONLINE
[ ] Stream video muncul di box kamera
[ ] Gerak di depan PIR:
    → Buzzer bunyi 1x beep
    → LCD tampilkan nama anak / PERINGATAN
    → Data muncul di tabel "Deteksi Wajah Anak (AI)"
[ ] Wajah tidak dikenal:
    → Buzzer 3x beep cepat
    → LCD "!! PERINGATAN !! / Wajah Tdk Dikenal"
    → Badge merah di tabel + alert merah di dashboard
[ ] Log aktivitas CCTV terupdate otomatis
[ ] Dashboard polling tanpa reload halaman (cek 10 detik)
```

---

## 📁 Struktur Proyek

```
panti-asuhan-kasih-agape/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/
│   │   │   ├── CctvController.php          ← API CCTV dari Pi
│   │   │   └── FaceRecognitionController.php ← API Face dari Pi
│   │   ├── CctvController.php              ← Dashboard web
│   │   └── ...
│   └── Models/
│       ├── CctvCamera.php
│       ├── CctvActivityLog.php
│       ├── FaceRecognitionLog.php
│       └── ...
├── recognition_engine/
│   ├── main_recognition.py                 ← 🚀 Entry point utama Pi
│   ├── sync_label_map.py                   ← Sync label DB ↔ Python
│   ├── models/lbph/
│   │   ├── trainer.yml                     ← Model LBPH terlatih
│   │   └── label_map_synced.json           ← Mapping index → child_id
│   ├── scripts/
│   │   ├── capture_dataset.py              ← Ambil foto dataset
│   │   ├── recognize_lbph.py               ← Test recognition
│   │   └── send_api.py                     ← HTTP client ke Laravel
│   └── vps_scripts/
│       └── yolo_activity_tracker.py        ← YOLO tracker (opsional)
├── routes/
│   ├── api.php                             ← API routes (dari Pi)
│   └── web.php                             ← Web routes
├── resources/views/
│   └── dashboard/cctv/
│       ├── index.blade.php                 ← Halaman CCTV monitoring
│       └── modals.blade.php                ← Modal tambah/edit kamera
├── .env.example
├── DEPLOYMENT-GUIDE.md
└── README.md
```

---

## 🆘 Troubleshooting

| Problem | Kemungkinan Penyebab | Solusi |
|---|---|---|
| LCD tidak muncul | Address salah | Cek `sudo i2cdetect -y 1`, ganti ke 0x3F |
| PIR selalu HIGH | Sensitivity terlalu tinggi | Putar potensiometer sensitivity ke minimum |
| Face tidak dikenal | Threshold terlalu ketat | Naikkan CONFIDENCE_THRESHOLD ke 85-90 |
| Badge OFFLINE terus | URL API salah / token beda | Cek API_BASE_URL dan token di Python |
| Stream tidak muncul | Port 5000 terblokir | Cek firewall Pi: `sudo ufw allow 5000` |
| RTSP gagal connect | IP/password CCTV salah | Test: `ffplay rtsp://...` di Pi |
| OOM di VPS 1GB | RAM kurang | Upgrade ke 2GB atau tambah swap |

---

## 👥 Peran Pengguna

| Role | Akses |
|---|---|
| **Admin** | Semua fitur + manajemen user + kamera + settings |
| **Pengasuh** | Dashboard, kehadiran, profil panti, galeri |
| **Donatur/Sponsor** | Lihat kehadiran anak, info panti, status donasi |

---

<div align="center">

**Dibuat untuk keperluan Tugas Akhir / Skripsi**

Sistem Monitoring Panti Asuhan Kasih Agape © 2025

</div>
