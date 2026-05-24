import cv2
import dlib
import numpy as np
from numpy.linalg import norm
import os

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DLIB_LANDMARK_PATH = os.path.join(BASE_DIR, "models", "dlib", "shape_predictor_68_face_landmarks.dat")
SMILE_CASCADE_PATH = os.path.join(BASE_DIR, "models", "haarcascades", "haarcascade_smile.xml")

_predictor = None
_smile_detector = None

def init_liveness():
    global _predictor, _smile_detector
    
    if not os.path.exists(DLIB_LANDMARK_PATH):
        print("[WARNING] File shape_predictor_68_face_landmarks.dat tidak ditemukan di models/dlib/")
    else:
        if _predictor is None:
            _predictor = dlib.shape_predictor(DLIB_LANDMARK_PATH)
            
    if not os.path.exists(SMILE_CASCADE_PATH):
        print("[WARNING] File haarcascade_smile.xml tidak ditemukan di models/haarcascades/")
    else:
        if _smile_detector is None:
            _smile_detector = cv2.CascadeClassifier(SMILE_CASCADE_PATH)

def _mid_line_distance(p1, p2, p3, p4):
    p5 = np.array([int((p1[0] + p2[0])/2), int((p1[1] + p2[1])/2)])
    p6 = np.array([int((p3[0] + p4[0])/2), int((p3[1] + p4[1])/2)])
    return norm(p5 - p6)

def _aspect_ratio(landmarks, eye_range):
    eye = np.array([np.array([landmarks.part(i).x, landmarks.part(i).y]) for i in eye_range])
    B = norm(eye[0] - eye[3])
    A = _mid_line_distance(eye[1], eye[2], eye[5], eye[4])
    if B == 0: return 0
    return A / B

def check_liveness(gray, rect):
    """
    Mengecek apakah wajah dalam kondisi hidup (mengedip atau tersenyum).
    Karena detektor wajah kita memakai format (x,y,w,h) dari DNN, kita perlu konversi ke dlib.rectangle
    """
    if _predictor is None or _smile_detector is None:
        init_liveness()
        if _predictor is None or _smile_detector is None:
            # Jika file tidak ada, anggap selalu lolos liveness (bypass sementara)
            return True, False, False 

    # Konversi koordinat OpenCV ke Dlib
    x, y, w, h = rect
    dlib_rect = dlib.rectangle(int(x), int(y), int(x + w), int(y + h))
    
    landmarks = _predictor(gray, dlib_rect)
    
    # 1. Cek Kedipan (EAR)
    left_ear = _aspect_ratio(landmarks, range(42, 48))
    right_ear = _aspect_ratio(landmarks, range(36, 42))
    ear = (left_ear + right_ear) / 2.0
    
    # Threshold kedipan normal biasanya < 0.2
    is_blinking = ear < 0.2
    
    # 2. Cek Senyuman
    face_roi_gray = gray[y:y+h, x:x+w]
    smiles = _smile_detector.detectMultiScale(face_roi_gray, scaleFactor=1.7, minNeighbors=22, minSize=(25, 25))
    is_smiling = len(smiles) > 0
    
    return True, is_blinking, is_smiling
