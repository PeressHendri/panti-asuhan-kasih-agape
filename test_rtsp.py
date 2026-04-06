import cv2
import os

print("Menguji beberapa kemungkinan URL RTSP...")
os.environ["OPENCV_FFMPEG_CAPTURE_OPTIONS"] = "rtsp_transport;tcp"

paths = [
    "rtsp://admin:Reinaldi494@192.168.1.2:8554/profile0",
    "rtsp://admin:Reinaldi494@192.168.1.2:8554/onvif1",
    "rtsp://admin:Reinaldi494@192.168.1.2:8554/onvif2",
    "rtsp://admin:Reinaldi494@192.168.1.2:8554/11",
    "rtsp://admin:Reinaldi494@192.168.1.2:8554/12",
    "rtsp://admin:Reinaldi494@192.168.1.2:8554/cam1/mpeg4",
    "rtsp://admin:Reinaldi494@192.168.1.2:8554/cam/realmonitor",
    "rtsp://192.168.1.2:8554/profile0",
    "rtsp://192.168.1.2:8554/onvif1",
    "rtsp://192.168.1.2:8554/11"
]

found = False
for path in paths:
    print(f"Mencoba: {path}")
    cap = cv2.VideoCapture(path)
    if cap.isOpened():
        ret, frame = cap.read()
        if ret:
            print(f"\n✅ BERHASIL! URL yang benar adalah:\n{path}\n")
            found = True
            break
    cap.release()

if not found:
    print("\n❌ Tidak ada yang berhasil. Masalah mungkin di koneksi UDP/TCP atau fiturnya belum tersimpan.")
