import os
import cv2
import time
import threading
import numpy as np
import subprocess
import sys
import requests

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
sys.path.append(os.path.join(BASE_DIR, "scripts"))
sys.path.append(os.path.join(BASE_DIR, "utils"))

from send_api import send_to_laravel
from face_detector import detect_faces_dnn
from predict_hybrid import predict_fusion
from liveness import check_liveness

# ============================================================
# CONFIG GPIO
# ============================================================
try:
    import RPi.GPIO as GPIO
    PIR_GPIO_AVAILABLE = True
except (ImportError, RuntimeError):
    PIR_GPIO_AVAILABLE = False

PIR_PIN        = 16   # Sensor PIR (hanya untuk kamera utama)
LCD_BACKLIGHT  = 18   # Pin kontrol backlight layar 3.5 inch

if PIR_GPIO_AVAILABLE:
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(PIR_PIN,       GPIO.IN)
    GPIO.setup(LCD_BACKLIGHT, GPIO.OUT)
    GPIO.output(LCD_BACKLIGHT, GPIO.LOW)  # Layar mati saat awal

# ============================================================
# CONFIG
# ============================================================
API_BASE_URL         = "https://pantiasuhankasihagape.id/api"
API_TOKEN            = "kasihagape2025secret"
CONFIDENCE_THRESHOLD = 75
VGG16_SIM_THRESHOLD  = 0.40

# Algoritma per mode:
#   RECOGNITION_ALGO_MAIN  = Untuk kamera utama (PIR + Speaker) → VGG16
#   RECOGNITION_ALGO_CCTV  = Untuk kamera CCTV dari website    → LBPH (lebih ringan)
RECOGNITION_ALGO_MAIN = "vgg16"
RECOGNITION_ALGO_CCTV = "lbph"

SLEEP_TIMEOUT   = 5   # Detik tanpa gerakan → layar mati
WAKE_HOLD_TIME  = 3   # Detik PIR aktif terus → layar nyala
FACE_MIN_SIZE   = (150, 150)
MIN_MOTION_AREA = 3000

from flask import Flask, Response
app = Flask(__name__)
latest_frames = {}
frame_locks   = {}

# ============================================================
# STATE LAYAR
# ============================================================
screen_state     = {"on": False}
pir_first_detect = {"time": None}
last_motion_time = {"time": time.time()}

def screen_on():
    if not screen_state["on"]:
        screen_state["on"] = True
        if PIR_GPIO_AVAILABLE:
            GPIO.output(LCD_BACKLIGHT, GPIO.HIGH)
        print("[LAYAR] ON")

def screen_off():
    if screen_state["on"]:
        screen_state["on"] = False
        if PIR_GPIO_AVAILABLE:
            GPIO.output(LCD_BACKLIGHT, GPIO.LOW)
        print("[LAYAR] OFF / SLEEP")

# ============================================================
# AUDIO
# ============================================================
def play_audio(pattern='success', nama=None):
    try:
        if pattern == 'success':
            text = f"Halo {nama}, kamu sudah absen." if nama else "Akses berhasil."
            subprocess.Popen(["espeak", "-v", "id", "-s", "140", text], stderr=subprocess.DEVNULL)
        elif pattern == 'warning':
            subprocess.Popen(["espeak", "-v", "id", "-s", "140", "Maaf, wajah belum terdaftar."], stderr=subprocess.DEVNULL)
    except FileNotFoundError:
        pass

# ============================================================
# AMBIL DAFTAR KAMERA DARI WEBSITE (API)
# Endpoint: GET /api/cctv/cameras
# Response: { "success": true, "data": [{ "kamera_id": "...", "rtsp_url": "...", ... }] }
# ============================================================
def get_cameras_from_web():
    try:
        headers  = {"Authorization": f"Bearer {API_TOKEN}", "Accept": "application/json"}
        response = requests.get(f"{API_BASE_URL}/cctv/cameras", headers=headers, timeout=10)
        if response.status_code == 200:
            data = response.json()
            cameras = data.get("data", [])
            print(f"[API] Berhasil mengambil {len(cameras)} kamera dari website.")
            return cameras
        else:
            print(f"[API] Gagal mengambil kamera. Status: {response.status_code}")
    except Exception as e:
        print(f"[API] Exception saat mengambil kamera: {e}")
    return []

# ============================================================
# LOAD MODEL LBPH (untuk CCTV)
# ============================================================
def load_lbph_model():
    recognizer = cv2.face.LBPHFaceRecognizer_create()
    MODEL_PATH     = os.path.join(BASE_DIR, "models", "lbph", "trainer.yml")
    LABEL_MAP_PATH = os.path.join(BASE_DIR, "models", "lbph", "label_map.pkl")
    label_map = {}
    if os.path.exists(MODEL_PATH) and os.path.exists(LABEL_MAP_PATH):
        recognizer.read(MODEL_PATH)
        import pickle
        with open(LABEL_MAP_PATH, "rb") as f:
            label_map = pickle.load(f)
        print("[INFO] Model LBPH berhasil dimuat.")
        return recognizer, label_map
    else:
        print("[WARNING] Model LBPH tidak ditemukan!")
        return None, {}

# ============================================================
# LOAD MODEL VGG16 (untuk kamera utama)
# ============================================================
def load_vgg16_model():
    try:
        from tensorflow.keras.models import load_model
        from tensorflow.keras.layers import Dense
        import pickle
        
        class SafeDense(Dense):
            def __init__(self, **kwargs):
                kwargs.pop('quantization_config', None)
                super().__init__(**kwargs)
                
        VGG16_MODEL_PATH = os.path.join(BASE_DIR, "models", "vgg16", "model_vgg16_adam.h5")
        ENCODER_PATH     = os.path.join(BASE_DIR, "models", "vgg16", "label_encoder.pkl")
        if os.path.exists(VGG16_MODEL_PATH) and os.path.exists(ENCODER_PATH):
            model = load_model(VGG16_MODEL_PATH, custom_objects={'Dense': SafeDense})
            with open(ENCODER_PATH, 'rb') as f:
                le = pickle.load(f)
            print("[INFO] Model VGG16 berhasil dimuat.")
            return model, le
        else:
            print("[WARNING] File model VGG16 tidak ditemukan!")
    except ImportError:
        print("[ERROR] TensorFlow tidak terinstall.")
    return None, None

# ============================================================
# HELPER: PREDIKSI WAJAH
# ============================================================
# Logika prediksi wajah (predict_lbph dan predict_vgg16) telah dipindahkan ke scripts/predict_hybrid.py 
# menggunakan teknik Ensemble / Fusion.

# ============================================================
# THREAD: KAMERA UTAMA (PIR + Speaker + VGG16 + Layar 3.5")
# ============================================================
def process_main_camera(camera_id, rtsp_url, model_vgg16, le_vgg16):
    frame_locks[camera_id]   = threading.Lock()
    latest_frames[camera_id] = None

    cap = cv2.VideoCapture(int(rtsp_url) if str(rtsp_url).isdigit() else rtsp_url)
    if not cap.isOpened():
        print(f"[MAIN CAM] Gagal membuka kamera: {rtsp_url}. Mencoba fallback index 1...")
        cap = cv2.VideoCapture(1)
        if not cap.isOpened():
            print("[MAIN CAM] Kamera fallback juga gagal.")
            return

    cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)
    cap.set(cv2.CAP_PROP_FRAME_WIDTH,  640)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)

    fgbg           = cv2.createBackgroundSubtractorMOG2(history=300, varThreshold=50, detectShadows=False)
    last_post_time = 0
    liveness_status = {} # Menyimpan status kedip & senyum per anak

    print(f"[MAIN CAM] Thread berjalan → {camera_id} | Algo: {RECOGNITION_ALGO_MAIN.upper()}")

    while True:
        ret, frame = cap.read()
        if not ret:
            time.sleep(0.05)
            continue

        pir_active      = False
        motion_detected = False
        now             = time.time()

        if PIR_GPIO_AVAILABLE and GPIO.input(PIR_PIN) == GPIO.HIGH:
            pir_active = motion_detected = True

        small_frame = cv2.resize(frame, (320, 240))
        fgmask      = fgbg.apply(small_frame)
        if cv2.countNonZero(fgmask) > (MIN_MOTION_AREA / 4):
            motion_detected = True

        # Logika Wake
        if pir_active or motion_detected:
            if pir_first_detect["time"] is None:
                pir_first_detect["time"] = now
            if now - pir_first_detect["time"] >= WAKE_HOLD_TIME:
                screen_on()
                last_motion_time["time"] = now
        else:
            pir_first_detect["time"] = None

        # Logika Sleep
        if screen_state["on"] and (now - last_motion_time["time"] >= SLEEP_TIMEOUT):
            screen_off()

        display_frame = frame.copy()

        if screen_state["on"] and motion_detected:
            gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
            faces = detect_faces_dnn(frame)

            for (x, y, w, h) in faces:
                is_recognized, nama, child_id, accuracy_pct, model_used = predict_fusion(
                    frame, x, y, w, h, model_vgg16, le_vgg16, None, None
                )

                liveness_passed = False
                color = (0, 0, 255)
                label = "Orang Asing"
                liveness_text = ""

                if is_recognized:
                    has_models, is_blinking, is_smiling = check_liveness(gray, (x, y, w, h))
                    
                    if child_id not in liveness_status:
                        liveness_status[child_id] = {"blink": False, "smile": False, "time": now}
                    
                    # Update status
                    if is_blinking: liveness_status[child_id]["blink"] = True
                    if is_smiling: liveness_status[child_id]["smile"] = True
                    liveness_status[child_id]["time"] = now # refresh expiry
                    
                    if not has_models:
                        liveness_passed = True # Bypass jika model dlib 99MB blm diinstall
                    else:
                        liveness_passed = liveness_status[child_id]["blink"] and liveness_status[child_id]["smile"]
                    
                    if liveness_passed:
                        label = f"{nama} ({accuracy_pct:.0f}%) [{model_used}]"
                        color = (0, 255, 0)
                        liveness_text = "LIVENESS PASSED!"
                    else:
                        label = f"{nama} - MENUNGGU LIVENESS"
                        color = (0, 255, 255) # Yellow
                        b_txt = "BERHASIL" if liveness_status[child_id]["blink"] else "TIDAK"
                        s_txt = "BERHASIL" if liveness_status[child_id]["smile"] else "TIDAK"
                        liveness_text = f"Kedip: {b_txt} | Senyum: {s_txt}"

                if is_recognized and liveness_passed and (now - last_post_time > 5):
                    last_post_time = now
                    play_audio('success', nama)
                    send_to_laravel(child_id=child_id, confidence_score=round(accuracy_pct, 2),
                                    status="check_in", kamera_id=camera_id, algoritma=RECOGNITION_ALGO_MAIN)
                    # Reset liveness setelah berhasil absen
                    liveness_status[child_id] = {"blink": False, "smile": False, "time": now}
                
                elif not is_recognized and now - last_post_time > 5:
                    last_post_time = now
                    play_audio('warning')

                cv2.rectangle(display_frame, (x, y), (x+w, y+h), color, 3)
                cv2.putText(display_frame, label, (x, y-10), cv2.FONT_HERSHEY_SIMPLEX, 0.6, color, 2)
                if liveness_text:
                    cv2.putText(display_frame, liveness_text, (x, y+h+20), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)

            # Cleanup memory (hapus status liveness anak yg sudah pergi > 10 detik)
            liveness_status = {k: v for k, v in liveness_status.items() if now - v["time"] < 10}

        elif not screen_state["on"]:
            display_frame = np.zeros((480, 640, 3), dtype=np.uint8)

        ok, buf = cv2.imencode('.jpg', display_frame)
        if ok:
            with frame_locks[camera_id]:
                latest_frames[camera_id] = buf.tobytes()
        time.sleep(0.01)

# ============================================================
# THREAD: KAMERA CCTV (MOG2 + LBPH, Tanpa PIR/Speaker)
# Untuk setiap kamera yang diambil dari website
# ============================================================
def process_cctv_stream(camera_id, rtsp_url, recognizer_lbph, label_map):
    frame_locks[camera_id]   = threading.Lock()
    latest_frames[camera_id] = None

    cap = cv2.VideoCapture(rtsp_url)
    if not cap.isOpened():
        print(f"[CCTV] Gagal membuka stream: {camera_id} → {rtsp_url}")
        return

    cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)
    cap.set(cv2.CAP_PROP_FRAME_WIDTH,  640)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)

    fgbg           = cv2.createBackgroundSubtractorMOG2(history=300, varThreshold=50, detectShadows=False)
    last_post_time = 0

    # Kirim status online ke website
    try:
        headers = {"Authorization": f"Bearer {API_TOKEN}", "Accept": "application/json"}
        requests.post(f"{API_BASE_URL}/cctv/status",
                      json={"kamera_id": camera_id, "is_online": True},
                      headers=headers, timeout=5)
    except Exception:
        pass

    print(f"[CCTV] Thread berjalan → {camera_id} | Algo: {RECOGNITION_ALGO_CCTV.upper()} + MOG2")

    while True:
        ret, frame = cap.read()
        if not ret:
            time.sleep(0.5)
            # Reconnect jika stream terputus
            cap.release()
            cap = cv2.VideoCapture(rtsp_url)
            continue

        motion_detected = False
        small_frame     = cv2.resize(frame, (320, 240))
        fgmask          = fgbg.apply(small_frame)
        if cv2.countNonZero(fgmask) > (MIN_MOTION_AREA / 4):
            motion_detected = True

        display_frame = frame.copy()
        now           = time.time()

        if motion_detected:
            faces = detect_faces_dnn(frame)

            for (x, y, w, h) in faces:
                is_recognized, nama, child_id, accuracy_pct, model_used = predict_fusion(
                    frame, x, y, w, h, None, None, recognizer_lbph, label_map, CONFIDENCE_THRESHOLD
                )

                label = f"{nama} ({accuracy_pct:.0f}%)" if is_recognized else "Orang Asing"
                color = (0, 255, 0) if is_recognized else (0, 0, 255)

                if is_recognized and now - last_post_time > 5:
                    last_post_time = now
                    send_to_laravel(child_id=child_id, confidence_score=round(accuracy_pct, 2),
                                    status="check_in", kamera_id=camera_id, algoritma=RECOGNITION_ALGO_CCTV)

                cv2.rectangle(display_frame, (x, y), (x+w, y+h), color, 3)
                cv2.putText(display_frame, label, (x, y-10), cv2.FONT_HERSHEY_SIMPLEX, 0.8, color, 2)

            # Kirim notif motion ke log aktivitas CCTV
            if now - last_post_time > 30:
                try:
                    headers = {"Authorization": f"Bearer {API_TOKEN}", "Accept": "application/json"}
                    requests.post(f"{API_BASE_URL}/cctv/activity",
                                  json={"kamera_id": camera_id,
                                        "jenis_aktivitas": "motion_detected",
                                        "keterangan": "Gerakan terdeteksi oleh MOG2"},
                                  headers=headers, timeout=5)
                except Exception:
                    pass
        else:
            cv2.putText(display_frame, f"CCTV STANDBY | {camera_id}", (20, 30),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)

        ok, buf = cv2.imencode('.jpg', display_frame)
        if ok:
            with frame_locks[camera_id]:
                latest_frames[camera_id] = buf.tobytes()
        time.sleep(0.03)

# ============================================================
# FLASK STREAMING
# ============================================================
@app.route('/video_feed/<cam_id>')
def video_feed(cam_id):
    def gen():
        while True:
            if cam_id in latest_frames and latest_frames[cam_id]:
                yield (b'--frame\r\nContent-Type: image/jpeg\r\n\r\n'
                       + latest_frames[cam_id] + b'\r\n')
            time.sleep(0.04)
    return Response(gen(), mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/cameras')
def list_cameras():
    """Endpoint untuk melihat kamera yang sedang aktif di Pi ini."""
    from flask import jsonify
    cam_list = [{"kamera_id": k, "streaming_url": f"/video_feed/{k}"} for k in latest_frames.keys()]
    return jsonify({"active_cameras": cam_list, "total": len(cam_list)})

# ============================================================
# MAIN
# ============================================================
if __name__ == "__main__":
    print("=" * 60)
    print("  Sistem Panti Asuhan Kasih Agape - Face Recognition")
    print("=" * 60)

    # 1. Load model VGG16 (kamera utama)
    model_vgg16, le_vgg16 = load_vgg16_model()

    # 2. Load model LBPH (kamera CCTV)
    recognizer_lbph, label_map = load_lbph_model()

    # 3. Jalankan kamera UTAMA (Webcam Pi + PIR + Speaker)
    main_cam_thread = threading.Thread(
        target=process_main_camera,
        args=("pintu_utama", 0, model_vgg16, le_vgg16),
        daemon=True
    )
    main_cam_thread.start()
    print("[MAIN] Thread kamera utama dijalankan.")

    # 4. Ambil daftar kamera CCTV dari website
    cctv_cameras = get_cameras_from_web()

    for cam in cctv_cameras:
        cam_id   = cam.get("kamera_id")
        rtsp_url = cam.get("rtsp_url")

        # Skip kamera utama (pintu_utama sudah dihandle di atas)
        if cam_id == "pintu_utama" or not rtsp_url:
            continue

        t = threading.Thread(
            target=process_cctv_stream,
            args=(cam_id, rtsp_url, recognizer_lbph, label_map),
            daemon=True
        )
        t.start()
        print(f"[CCTV] Thread kamera {cam_id} dijalankan → {rtsp_url}")
        time.sleep(1)  # Jeda kecil agar tidak semua thread start bersamaan

    # 5. Jalankan Flask server untuk streaming ke website
    flask_thread = threading.Thread(
        target=lambda: app.run(host='0.0.0.0', port=5050, threaded=True),
        daemon=True
    )
    flask_thread.start()
    print(f"[FLASK] Streaming server aktif di http://0.0.0.0:5050")
    print(f"        Wake: {WAKE_HOLD_TIME}dtk | Sleep: {SLEEP_TIMEOUT}dtk")

    # 6. Monitor lokal di layar 3.5 inch (Main Thread)
    while True:
        if "pintu_utama" in latest_frames and latest_frames["pintu_utama"]:
            nparr = np.frombuffer(latest_frames["pintu_utama"], np.uint8)
            img   = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            if img is not None:
                cv2.namedWindow("Monitor Panti Agape", cv2.WND_PROP_FULLSCREEN)
                cv2.setWindowProperty("Monitor Panti Agape", cv2.WND_PROP_FULLSCREEN, cv2.WINDOW_FULLSCREEN)
                cv2.imshow("Monitor Panti Agape", img)

        if cv2.waitKey(1) & 0xFF == ord('q'):
            break
        time.sleep(0.01)

    cv2.destroyAllWindows()
    if PIR_GPIO_AVAILABLE:
        GPIO.cleanup()
