import os
import sys
import cv2
import requests

BASE_DIR  = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODEL_DIR = os.path.join(BASE_DIR, "models", "dnn")

PROTOTXT_URL = "https://raw.githubusercontent.com/opencv/opencv/master/samples/dnn/face_detector/deploy.prototxt"
MODEL_URL    = "https://raw.githubusercontent.com/opencv/opencv_3rdparty/dnn_samples_face_detector_20180205_fp16/res10_300x300_ssd_iter_140000_fp16.caffemodel"

PROTOTXT_PATH = os.path.join(MODEL_DIR, "deploy.prototxt")
MODEL_PATH    = os.path.join(MODEL_DIR, "res10_300x300_ssd_iter_140000_fp16.caffemodel")

_net               = None
_download_attempted = False   # Cegah download berulang di setiap panggilan


# ─────────────────────────────────────────────────────────────────────────────
# DOWNLOAD OTOMATIS (jika belum ada)
# - prototxt  : kecil (~28KB)  → timeout singkat cukup
# - caffemodel: ~5MB           → pakai stream + timeout lebih besar
# ─────────────────────────────────────────────────────────────────────────────
def _download_models():
    global _download_attempted
    if _download_attempted:
        return
    _download_attempted = True

    os.makedirs(MODEL_DIR, exist_ok=True)

    try:
        # --- 1. Prototxt (kecil, ~28KB) ---
        if not os.path.exists(PROTOTXT_PATH):
            print("[FaceDetector] Mengunduh prototxt...", file=sys.stderr)
            res = requests.get(PROTOTXT_URL, timeout=(5, 30))
            res.raise_for_status()
            with open(PROTOTXT_PATH, 'wb') as f:
                f.write(res.content)
            print("[FaceDetector] prototxt selesai diunduh.", file=sys.stderr)

        # --- 2. Caffemodel (~5MB) pakai streaming agar tidak timeout ---
        if not os.path.exists(MODEL_PATH):
            print("[FaceDetector] Mengunduh caffemodel (~5MB), harap tunggu...", file=sys.stderr)
            res = requests.get(MODEL_URL, timeout=(5, 120), stream=True)
            res.raise_for_status()
            tmp_path = MODEL_PATH + ".tmp"
            with open(tmp_path, 'wb') as f:
                for chunk in res.iter_content(chunk_size=8192):
                    if chunk:
                        f.write(chunk)
            os.rename(tmp_path, MODEL_PATH)   # Atomic replace agar file tidak korup jika interrupted
            print("[FaceDetector] caffemodel selesai diunduh.", file=sys.stderr)

    except Exception as e:
        print(f"[FaceDetector] Download gagal: {e}. Akan pakai Haar Cascade sebagai fallback.", file=sys.stderr)
        # Hapus file .tmp yang mungkin tersisa agar tidak terbaca sebagai file korup
        tmp_path = MODEL_PATH + ".tmp"
        if os.path.exists(tmp_path):
            os.remove(tmp_path)


def _load_net():
    global _net
    if _net is None:
        _download_models()
        if os.path.exists(PROTOTXT_PATH) and os.path.exists(MODEL_PATH):
            try:
                _net = cv2.dnn.readNetFromCaffe(PROTOTXT_PATH, MODEL_PATH)
                # Optimasi untuk Raspberry Pi (jika OpenVINO/Movidius tersedia akan lebih cepat,
                # jika tidak akan fallback ke CPU secara otomatis)
                _net.setPreferableBackend(cv2.dnn.DNN_BACKEND_OPENCV)
                _net.setPreferableTarget(cv2.dnn.DNN_TARGET_CPU)
                print("[FaceDetector] DNN face detector berhasil dimuat.", file=sys.stderr)
            except Exception as e:
                print(f"[FaceDetector] Gagal memuat DNN model: {e}", file=sys.stderr)
                _net = None
    return _net


# ─────────────────────────────────────────────────────────────────────────────
# FALLBACK: OpenCV Haar Cascade (sudah termasuk dalam instalasi OpenCV,
#           tidak perlu install tambahan apapun, langsung tersedia di Pi)
# ─────────────────────────────────────────────────────────────────────────────
_haar_cascade = None

def _get_haar_cascade():
    global _haar_cascade
    if _haar_cascade is None:
        haar_path = os.path.join(cv2.data.haarcascades, 'haarcascade_frontalface_default.xml')
        if os.path.exists(haar_path):
            _haar_cascade = cv2.CascadeClassifier(haar_path)
            print("[FaceDetector] Haar Cascade fallback dimuat.", file=sys.stderr)
        else:
            print("[FaceDetector] PERINGATAN: Haar Cascade tidak ditemukan!", file=sys.stderr)
    return _haar_cascade


def _detect_faces_haar(img):
    """Deteksi wajah menggunakan Haar Cascade bawaan OpenCV sebagai fallback."""
    cascade = _get_haar_cascade()
    if cascade is None:
        return []
    gray  = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    faces = cascade.detectMultiScale(
        gray, scaleFactor=1.1, minNeighbors=5,
        minSize=(30, 30), flags=cv2.CASCADE_SCALE_IMAGE
    )
    if len(faces) == 0:
        return []
    return [(int(x), int(y), int(w), int(h)) for (x, y, w, h) in faces]


# ─────────────────────────────────────────────────────────────────────────────
# FUNGSI UTAMA
# ─────────────────────────────────────────────────────────────────────────────
def detect_faces_dnn(img, confidence_threshold=0.5):
    """
    Mendeteksi wajah menggunakan cv2.DNN (ResNet-10 SSD).
    Sangat tangguh terhadap kemiringan wajah, masker, kacamata, dan pencahayaan ekstrem.
    Jika DNN tidak tersedia (offline/model belum diunduh), otomatis fallback ke
    OpenCV Haar Cascade yang sudah terpasang bersama OpenCV — tidak perlu install tambahan.
    Returns: list of (x, y, w, h)
    """
    net = _load_net()
    if net is None:
        # Fallback ke Haar Cascade bawaan OpenCV (tidak butuh dlib / koneksi internet)
        return _detect_faces_haar(img)

    h, w = img.shape[:2]
    # Blob standar untuk Caffe face detector OpenCV (300x300, mean subtraction BGR)
    blob = cv2.dnn.blobFromImage(
        cv2.resize(img, (300, 300)), 1.0, (300, 300), (104.0, 177.0, 123.0)
    )
    net.setInput(blob)
    detections = net.forward()

    faces = []
    for i in range(0, detections.shape[2]):
        conf = detections[0, 0, i, 2]
        if conf > confidence_threshold:
            box = detections[0, 0, i, 3:7] * [w, h, w, h]
            (startX, startY, endX, endY) = box.astype("int")
            startX, startY = max(0, startX), max(0, startY)
            endX,   endY   = min(w, endX),   min(h, endY)
            width  = endX - startX
            height = endY - startY
            if width > 30 and height > 30:
                faces.append((startX, startY, width, height))

    return faces
