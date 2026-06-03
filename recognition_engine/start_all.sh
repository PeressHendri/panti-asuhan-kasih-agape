#!/bin/bash
# ==============================================================================
#  Sistem Panti Asuhan Kasih Agape - CCTV & Attendance Auto Launcher
# ==============================================================================

# 1. Hentikan sisa proses tunnel yang lama jika ada agar tidak bentrok
echo -e "\e[33m[INFO]\e[0m Menghentikan sisa proses Cloudflare Tunnel lama..."
pkill -f "cloudflared tunnel run" 2>/dev/null
sleep 1

# 2. Jalankan Cloudflare Tunnel di latar belakang (Background)
echo -e "\e[32m[INFO]\e[0m Menjalankan Cloudflare Tunnel di background..."
nohup cloudflared tunnel run --url http://localhost:5050 cctv-panti > ~/cloudflared_tunnel.log 2>&1 &

# Tunggu sebentar agar tunnel siap
sleep 2
echo -e "\e[32m[INFO]\e[0m Cloudflare Tunnel sukses berjalan di latar belakang! (Log disimpan di ~/cloudflared_tunnel.log)"

# 3. Jalankan Program Utama Absensi & CCTV
echo -e "\e[34m[INFO]\e[0m Menjalankan Mesin Utama Absensi & CCTV MOG2..."
echo "=============================================================================="
python3 main_recognition.py
