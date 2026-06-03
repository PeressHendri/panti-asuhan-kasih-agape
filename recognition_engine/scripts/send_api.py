import requests
import json
import time
import os
from datetime import datetime

# ==========================================
# [GAP 2 FIX] KONFIGURASI API
# Endpoint diperbaiki dari /api/face-log ke /api/face-recognition
# sesuai routes/api.php dan FaceRecognitionController@store
# ==========================================
API_BASE_URL = "https://pantiasuhankasihagape.id/api"
API_URL = f"{API_BASE_URL}/face-recognition"
API_TOKEN = "kasihagape2025secret"  # Harus sama dengan RASPBERRY_PI_TOKEN di .env Laravel

AUTH_HEADERS = {
    'Authorization': f'Bearer {API_TOKEN}',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

PENDING_LOGS_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pending_logs.json")
LOG_DIR           = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "logs")

def get_log_file():
    if not os.path.exists(LOG_DIR):
        os.makedirs(LOG_DIR)
    date_str = datetime.now().strftime("%Y%m%d")
    return os.path.join(LOG_DIR, f"log_{date_str}.txt")

def write_log(message):
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    with open(get_log_file(), "a") as f:
        f.write(f"[{timestamp}] {message}\n")

def save_pending(data):
    pending = []
    if os.path.exists(PENDING_LOGS_FILE):
        try:
            with open(PENDING_LOGS_FILE, "r") as f:
                pending = json.load(f)
        except:
            pending = []
    
    pending.append(data)
    with open(PENDING_LOGS_FILE, "w") as f:
        json.dump(pending, f)
    write_log("Saved to pending_logs.json due to failure")

def send_to_laravel(child_id, confidence_score, status="check_in", kamera_id="webcam_pintu", algoritma="lbph"):
    """
    Kirim data pengenalan wajah ke Laravel API.
    
    Args:
        child_id        : ID anak di database (int) atau None jika tidak dikenal
        confidence_score: Skor akurasi 0-100 (float)
        status          : 'check_in', 'check_out', atau 'tidak_dikenal'
        kamera_id       : ID kamera sumber (str), sesuaikan dengan cctv_cameras.kamera_id
        algoritma       : 'lbph' atau 'cnn'
    """
    # [GAP 2 FIX] Payload disesuaikan dengan FaceRecognitionController@store
    payload = {
        "child_id"         : child_id,
        "confidence_score" : round(float(confidence_score), 2),
        "algoritma"        : algoritma,
        "status"           : status,        # check_in / check_out / tidak_dikenal
        "kamera_id"        : kamera_id,
    }

    try:
        response = requests.post(API_URL, json=payload, headers=AUTH_HEADERS, timeout=5)
        if response.status_code in [200, 201]:
            write_log(f"api sent success | child_id={child_id} | conf={confidence_score} | status={status}")
            return True
        else:
            write_log(f"api sent failed (status {response.status_code}): {response.text[:200]}")
            save_pending(payload)
            return False
    except Exception as e:
        write_log(f"api sent failed error: {str(e)}")
        save_pending(payload)
        return False

def retry_pending():
    if not os.path.exists(PENDING_LOGS_FILE):
        return

    try:
        with open(PENDING_LOGS_FILE, "r") as f:
            pending = json.load(f)
    except:
        return

    if not pending:
        return

    remaining = []
    write_log(f"Attempting to retry {len(pending)} pending logs")
    
    for item in pending:
        try:
            # [GAP 2 FIX] Gunakan AUTH_HEADERS saat retry juga
            response = requests.post(API_URL, json=item, headers=AUTH_HEADERS, timeout=5)
            if response.status_code in [200, 201]:
                write_log(f"Retry success for child_id: {item.get('child_id')}")
            else:
                remaining.append(item)
        except:
            remaining.append(item)
    
    with open(PENDING_LOGS_FILE, "w") as f:
        json.dump(remaining, f)

if __name__ == "__main__":
    print("[send_api.py] Test kirim ke endpoint /api/face-recognition ...")
    # Contoh test, hapus # untuk mencoba:
    # send_to_laravel(child_id=1, confidence_score=87.4, status='check_in', kamera_id='webcam_pintu')
    print(f"API_URL  : {API_URL}")
    print(f"API_TOKEN: {API_TOKEN[:10]}...")
