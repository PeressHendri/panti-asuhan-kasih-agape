import cv2
import numpy as np
from numpy.linalg import norm
import os
import sys

try:
    import dlib
    DLIB_AVAILABLE = True
except ImportError:
    DLIB_AVAILABLE = False

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

DLIB_LANDMARK_PATH = os.path.join(BASE_DIR, "models", "dlib", "shape_predictor_68_face_landmarks.dat")

# Smile cascade: coba path kustom dulu, fallback ke bawaan OpenCV
_SMILE_CASCADE_CUSTOM  = os.path.join(BASE_DIR, "models", "haarcascades", "haarcascade_smile.xml")
_SMILE_CASCADE_BUILTIN = os.path.join(cv2.data.haarcascades, "haarcascade_smile.xml")
SMILE_CASCADE_PATH = (
    _SMILE_CASCADE_CUSTOM if os.path.exists(_SMILE_CASCADE_CUSTOM)
    else _SMILE_CASCADE_BUILTIN
)

_predictor      = None
_smile_detector = None
_init_attempted = False   # Cegah init berulang setiap frame saat file model tidak ada


# ─────────────────────────────────────────────────────────────────────────────
# INISIALISASI
# ─────────────────────────────────────────────────────────────────────────────
def init_liveness():
    """
    Muat model dlib shape predictor dan smile cascade.
    Hanya dijalankan sekali; flag _init_attempted mencegah loop peringatan.
    """
    global _predictor, _smile_detector, _init_attempted
    _init_attempted = True

    if not DLIB_AVAILABLE:
        return

    # --- 1. dlib shape predictor (untuk EAR kedipan) ---
    if not os.path.exists(DLIB_LANDMARK_PATH):
        print(
            "[Liveness] PERINGATAN: shape_predictor_68_face_landmarks.dat tidak ditemukan.\n"
            "           Unduh dari: http://dlib.net/files/shape_predictor_68_face_landmarks.dat.bz2\n"
            "           Letakkan di: recognition_engine/models/dlib/\n"
            "           Liveness check akan di-bypass hingga file tersedia.",
            file=sys.stderr
        )
    else:
        if _predictor is None:
            try:
                _predictor = dlib.shape_predictor(DLIB_LANDMARK_PATH)
                print("[Liveness] dlib shape predictor berhasil dimuat.", file=sys.stderr)
            except Exception as e:
                print(f"[Liveness] Gagal memuat shape predictor: {e}", file=sys.stderr)

    # --- 2. Smile cascade ---
    if not os.path.exists(SMILE_CASCADE_PATH):
        print(
            "[Liveness] PERINGATAN: haarcascade_smile.xml tidak ditemukan.\n"
            "           Liveness check senyum akan di-bypass.",
            file=sys.stderr
        )
    else:
        if _smile_detector is None:
            try:
                _smile_detector = cv2.CascadeClassifier(SMILE_CASCADE_PATH)
                src = "bawaan OpenCV" if SMILE_CASCADE_PATH == _SMILE_CASCADE_BUILTIN else "kustom"
                print(f"[Liveness] Smile cascade ({src}) berhasil dimuat.", file=sys.stderr)
            except Exception as e:
                print(f"[Liveness] Gagal memuat smile cascade: {e}", file=sys.stderr)


# ─────────────────────────────────────────────────────────────────────────────
# HELPER GEOMETRI
# ─────────────────────────────────────────────────────────────────────────────
def _mid_line_distance(p1, p2, p3, p4):
    p5 = np.array([int((p1[0] + p2[0]) / 2), int((p1[1] + p2[1]) / 2)])
    p6 = np.array([int((p3[0] + p4[0]) / 2), int((p3[1] + p4[1]) / 2)])
    return norm(p5 - p6)


def _aspect_ratio(landmarks, eye_range):
    eye = np.array([
        np.array([landmarks.part(i).x, landmarks.part(i).y])
        for i in eye_range
    ])
    B = norm(eye[0] - eye[3])
    A = _mid_line_distance(eye[1], eye[2], eye[5], eye[4])
    if B == 0:
        return 0
    return A / B


# ─────────────────────────────────────────────────────────────────────────────
# FUNGSI UTAMA
# ─────────────────────────────────────────────────────────────────────────────
def check_liveness(gray, rect):
    """
    Mengecek apakah wajah dalam kondisi hidup (mengedip ATAU tersenyum).

    Returns:
        (has_models, is_blinking, is_smiling)
        - has_models=False → liveness di-bypass otomatis (file model belum ada / dlib belum install)
        - has_models=True  → is_blinking & is_smiling berdasarkan deteksi nyata
    """
    global _init_attempted

    # 1. Pastikan init sudah dijalankan (sekali saja)
    if not _init_attempted:
        init_liveness()

    # 2. Bypass jika dlib tidak terinstall
    if not DLIB_AVAILABLE:
        return False, True, True

    # 3. Bypass jika salah satu model tidak berhasil dimuat
    if _predictor is None or _smile_detector is None:
        return False, True, True

    try:
        x, y, w, h = rect

        # Konversi koordinat OpenCV → dlib
        dlib_rect = dlib.rectangle(int(x), int(y), int(x + w), int(y + h))
        landmarks = _predictor(gray, dlib_rect)

        # --- EAR (Eye Aspect Ratio) untuk deteksi kedipan ---
        left_ear  = _aspect_ratio(landmarks, range(42, 48))
        right_ear = _aspect_ratio(landmarks, range(36, 42))
        ear = (left_ear + right_ear) / 2.0
        is_blinking = ear < 0.2   # Threshold kedipan normal < 0.2

        # --- Deteksi senyuman via Haar Cascade ---
        face_roi_gray = gray[y:y + h, x:x + w]
        smiles        = _smile_detector.detectMultiScale(
            face_roi_gray, scaleFactor=1.7, minNeighbors=22, minSize=(25, 25)
        )
        is_smiling = len(smiles) > 0

        return True, is_blinking, is_smiling

    except Exception as e:
        # Jika terjadi error kalkulasi landmark, bypass agar tidak crash
        print(f"[Liveness] Exception saat check_liveness: {e}", file=sys.stderr)
        return False, True, True


# ─────────────────────────────────────────────────────────────────────────────
# Inisialisasi saat modul pertama kali di-import
# (bukan di setiap frame, cukup sekali di awal)
# ─────────────────────────────────────────────────────────────────────────────
init_liveness()
