import os
import cv2
import urllib.request

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODEL_DIR = os.path.join(BASE_DIR, "models", "dnn")

PROTOTXT_URL = "https://raw.githubusercontent.com/opencv/opencv/master/samples/dnn/face_detector/deploy.prototxt"
MODEL_URL = "https://raw.githubusercontent.com/opencv/opencv_3rdparty/dnn_samples_face_detector_20180205_fp16/res10_300x300_ssd_iter_140000_fp16.caffemodel"

PROTOTXT_PATH = os.path.join(MODEL_DIR, "deploy.prototxt")
MODEL_PATH = os.path.join(MODEL_DIR, "res10_300x300_ssd_iter_140000_fp16.caffemodel")

_net = None

def _download_models():
    os.makedirs(MODEL_DIR, exist_ok=True)
    if not os.path.exists(PROTOTXT_PATH):
        print("[FaceDetector] Downloading prototxt...")
        try: urllib.request.urlretrieve(PROTOTXT_URL, PROTOTXT_PATH)
        except Exception: pass
    if not os.path.exists(MODEL_PATH):
        print("[FaceDetector] Downloading caffemodel...")
        try: urllib.request.urlretrieve(MODEL_URL, MODEL_PATH)
        except Exception: pass

def _load_net():
    global _net
    if _net is None:
        _download_models()
        if os.path.exists(PROTOTXT_PATH) and os.path.exists(MODEL_PATH):
            _net = cv2.dnn.readNetFromCaffe(PROTOTXT_PATH, MODEL_PATH)
            # Optimasi untuk Raspberry Pi (jika OpenVINO/Movidius tersedia, ini akan mempercepat. Jika tidak, fallback ke CPU)
            _net.setPreferableBackend(cv2.dnn.DNN_BACKEND_OPENCV)
            _net.setPreferableTarget(cv2.dnn.DNN_TARGET_CPU)
    return _net

def detect_faces_dnn(img, confidence_threshold=0.5):
    """
    Mendeteksi wajah menggunakan cv2.DNN (ResNet-10 SSD).
    Sangat tangguh terhadap kemiringan wajah, masker, kacamata, dan pencahayaan ekstrem.
    Returns: list of (x, y, w, h)
    """
    net = _load_net()
    if net is None:
        # Fallback ke dlib/Haar jika download gagal/offline
        import dlib
        detector = dlib.get_frontal_face_detector()
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        faces = detector(gray, 1)
        res = []
        for f in faces:
            x, y, w, h = f.left(), f.top(), f.right() - f.left(), f.bottom() - f.top()
            res.append((max(0, x), max(0, y), w, h))
        return res

    h, w = img.shape[:2]
    # Blob untuk Caffe model OpenCV standard face detector (300x300, mean subtraction)
    blob = cv2.dnn.blobFromImage(cv2.resize(img, (300, 300)), 1.0, (300, 300), (104.0, 177.0, 123.0))
    net.setInput(blob)
    detections = net.forward()

    faces = []
    for i in range(0, detections.shape[2]):
        conf = detections[0, 0, i, 2]
        if conf > confidence_threshold:
            box = detections[0, 0, i, 3:7] * [w, h, w, h]
            (startX, startY, endX, endY) = box.astype("int")
            startX, startY = max(0, startX), max(0, startY)
            endX, endY = min(w, endX), min(h, endY)
            width = endX - startX
            height = endY - startY
            if width > 30 and height > 30:
                faces.append((startX, startY, width, height))
                
    return faces
