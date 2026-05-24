import cv2
import os
import sys
import pickle

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.append(os.path.join(BASE_DIR, "utils"))
sys.path.append(os.path.join(BASE_DIR, "scripts"))

from face_detector import detect_faces_dnn
from predict_hybrid import predict_fusion

LBPH_MODEL = os.path.join(BASE_DIR, "models", "lbph", "trainer.yml")
LBPH_MAP = os.path.join(BASE_DIR, "models", "lbph", "label_map.pkl")
VGG16_MODEL = os.path.join(BASE_DIR, "models", "vgg16", "model_vgg16_adam.h5")
VGG16_ENC = os.path.join(BASE_DIR, "models", "vgg16", "label_encoder.pkl")

def main():
    print("=== TEST LIVE WEBCAM (HYBRID VGG16 + LBPH) ===")
    
    # 1. Load Models
    model_vgg = None
    le_vgg = None
    recognizer = None
    label_map = {}

    print("[1/2] Loading VGG16 Model...")
    try:
        from tensorflow.keras.models import load_model
        from tensorflow.keras.layers import Dense
        class SafeDense(Dense):
            def __init__(self, **kwargs):
                kwargs.pop('quantization_config', None)
                super().__init__(**kwargs)
                
        if os.path.exists(VGG16_MODEL) and os.path.exists(VGG16_ENC):
            model_vgg = load_model(VGG16_MODEL, custom_objects={'Dense': SafeDense})
            with open(VGG16_ENC, 'rb') as f:
                le_vgg = pickle.load(f)
            print("  -> VGG16 Loaded Successfully!")
        else:
            print("  -> VGG16 files not found.")
    except Exception as e:
        print(f"  -> Failed to load VGG16: {e}")

    print("[2/2] Loading LBPH Model...")
    if os.path.exists(LBPH_MODEL) and os.path.exists(LBPH_MAP):
        try:
            recognizer = cv2.face.LBPHFaceRecognizer_create()
            recognizer.read(LBPH_MODEL)
            with open(LBPH_MAP, 'rb') as f:
                label_map = pickle.load(f)
            print("  -> LBPH Loaded Successfully!")
        except Exception as e:
            print(f"  -> Failed to load LBPH: {e}")
    else:
        print("  -> LBPH files not found.")

    if model_vgg is None and recognizer is None:
        print("ERROR: No models loaded. Cannot test recognition.")
        return

    # 2. Open Webcam
    print("\nStarting Webcam... Tekan 'q' pada keyboard untuk keluar.")
    cap = cv2.VideoCapture(0)
    
    if not cap.isOpened():
        print("ERROR: Tidak bisa membuka webcam bawaan laptop.")
        return

    while True:
        ret, frame = cap.read()
        if not ret:
            break

        # Flip frame like a mirror
        frame = cv2.flip(frame, 1)

        # Detect faces
        faces = detect_faces_dnn(frame)
        
        for (x, y, w, h) in faces:
            # Predict using fusion
            is_rec, nama, cid, conf, model_used = predict_fusion(
                frame, x, y, w, h, model_vgg, le_vgg, recognizer, label_map, confidence_threshold=70
            )

            # Draw Box
            color = (0, 255, 0) if is_rec else (0, 0, 255)
            cv2.rectangle(frame, (x, y), (x + w, y + h), color, 2)
            
            # Label
            text = f"{nama} ({conf:.1f}%) [{model_used}]"
            cv2.putText(frame, text, (x, y - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.6, color, 2)
            
            # Tampilkan raw output jika unknown agar kita tahu score sebenarnya
            if not is_rec:
                cv2.putText(frame, f"Raw Score: {conf:.1f}%", (x, y + h + 25), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 255), 1)

        # Show Window
        cv2.imshow("Test Face Recognition", frame)

        # Exit on 'q'
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cap.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    main()
