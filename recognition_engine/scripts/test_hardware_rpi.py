import time
import cv2

try:
    import RPi.GPIO as GPIO
    PIR_GPIO_AVAILABLE = True
except (ImportError, RuntimeError):
    PIR_GPIO_AVAILABLE = False
    print("❌ Library RPi.GPIO tidak ditemukan. Script ini harus dijalankan di Raspberry Pi!")

# BCM PIN Configuration (Samakan dengan main_recognition.py)
PIR_PIN = 16    # Physical Pin 36 (Aman dari LCD)
BUZZER_PIN = 26 # Physical Pin 37 (Opsional jika pakai buzzer)

# Setup GPIO
if PIR_GPIO_AVAILABLE:
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(PIR_PIN, GPIO.IN)
    print("✅ GPIO Berhasil di-setup!")

# Test Kamera 
print("Mengecek Kamera USB (Logitech)...")
cap = cv2.VideoCapture(0)
if cap.isOpened():
    ret, frame = cap.read()
    if ret:
        print("✅ Kamera Terdeteksi dan frame berhasil diambil!")
    else:
        print("❌ Kamera terbaca tapi tidak ada gambar masuk!")
else:
    print("❌ Kamera Gagal Diakses! Coba cek kabel USB biru-nya.")

cap.release()

if PIR_GPIO_AVAILABLE:
    print("\n==================================")
    print("🚀 SISTEM SIAP! MULAI TEST SENSOR...")
    print("Cobalah bergerak di depan sensor PIR")
    print("Tekan CTRL+C untuk berhenti pengecekan.")
    print("==================================\n")

    try:
        while True:
            if GPIO.input(PIR_PIN) == GPIO.HIGH:
                print("🚨 GERAKAN TERDETEKSI! Memutar audio sapaan via Speaker...")
                
                # Uji Coba Audio
                import subprocess
                subprocess.Popen(["espeak", "-v", "id", "-s", "140", "Sistem siap beroperasi. Gerakan terdeteksi."], stderr=subprocess.DEVNULL)
                
                # Kasih batas jeda sebelum scan ulang
                time.sleep(4) 
            time.sleep(0.1)
    except KeyboardInterrupt:
        print("\nTest Dihentikan oleh user.")
        GPIO.cleanup()
