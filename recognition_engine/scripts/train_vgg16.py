"""
Script Training VGG16 — Panti Asuhan Kasih Agape
Transfer Learning dari VGG16 ImageNet dengan augmentasi agresif.
Jalankan: python3 train_vgg16.py
"""
import os, sys, pickle
import numpy as np
from pathlib import Path

# Path bootstrap
import glob
for _sp in glob.glob(os.path.expanduser('~/.local/lib/python3.*/site-packages')):
    if _sp not in sys.path:
        sys.path.insert(0, _sp)

os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'

import cv2
from sklearn.preprocessing import LabelEncoder
from sklearn.utils import class_weight
import tensorflow as tf
from tensorflow.keras.applications import VGG16
from tensorflow.keras.models import Model
from tensorflow.keras.layers import Dense, Dropout, GlobalAveragePooling2D, BatchNormalization
from tensorflow.keras.optimizers import Adam
from tensorflow.keras.callbacks import ModelCheckpoint, EarlyStopping, ReduceLROnPlateau
from tensorflow.keras.preprocessing.image import ImageDataGenerator

BASE_DIR    = Path(__file__).parent.parent
DATASET_DIR = BASE_DIR / "dataset" / "train"
VAL_DIR     = BASE_DIR / "dataset" / "val"
OUTPUT_DIR  = BASE_DIR / "models" / "vgg16"
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

IMG_SIZE   = 224
BATCH_SIZE = 16
EPOCHS     = 50

print("=" * 55)
print("  Training VGG16 — Panti Asuhan Kasih Agape")
print("=" * 55)

# ── 1. Load dataset ──────────────────────────────────────────
def load_dataset(folder):
    X, y = [], []
    folder = Path(folder)
    for child_dir in sorted(folder.iterdir()):
        if not child_dir.is_dir(): continue
        label = child_dir.name
        imgs  = list(child_dir.glob("*.jpg")) + list(child_dir.glob("*.png"))
        for img_path in imgs:
            img = cv2.imread(str(img_path))
            if img is None: continue
            img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
            img = cv2.resize(img, (IMG_SIZE, IMG_SIZE))
            X.append(img)
            y.append(label)
    return np.array(X, dtype='float32'), np.array(y)

print("\n[1/5] Loading dataset train...")
X_train, y_train = load_dataset(DATASET_DIR)
print(f"      Train: {len(X_train)} foto, {len(set(y_train))} anak")

print("[1/5] Loading dataset val...")
X_val, y_val = load_dataset(VAL_DIR)
print(f"      Val  : {len(X_val)} foto")

# ── 2. Encode label ──────────────────────────────────────────
le = LabelEncoder()
le.fit(y_train)
y_train_enc = le.transform(y_train)
y_val_enc   = le.transform(y_val)
n_classes   = len(le.classes_)
print(f"\n[2/5] Total kelas: {n_classes} anak")

# ── 3. Preprocessing & Augmentasi ────────────────────────────
from tensorflow.keras.applications.vgg16 import preprocess_input

X_train_pp = preprocess_input(X_train.copy())
X_val_pp   = preprocess_input(X_val.copy())

# Augmentasi agresif untuk dataset kecil
datagen = ImageDataGenerator(
    rotation_range=20,
    width_shift_range=0.15,
    height_shift_range=0.15,
    shear_range=0.1,
    zoom_range=0.15,
    horizontal_flip=True,
    brightness_range=[0.7, 1.3],
    fill_mode='nearest'
)
datagen.fit(X_train_pp)

print("[3/5] Augmentasi aktif: rotasi, flip, zoom, brightness")

# ── 4. Bangun Model ──────────────────────────────────────────
print("\n[4/5] Membangun model VGG16...")
base = VGG16(weights='imagenet', include_top=False, input_shape=(IMG_SIZE, IMG_SIZE, 3))

# Freeze semua layer VGG16 dulu (fase 1)
for layer in base.layers:
    layer.trainable = False

x = base.output
x = GlobalAveragePooling2D()(x)
x = BatchNormalization()(x)
x = Dense(512, activation='relu')(x)
x = Dropout(0.5)(x)
x = Dense(256, activation='relu')(x)
x = Dropout(0.3)(x)
out = Dense(n_classes, activation='softmax')(x)

model = Model(inputs=base.input, outputs=out)
model.compile(
    optimizer=Adam(learning_rate=1e-3),
    loss='sparse_categorical_crossentropy',
    metrics=['accuracy']
)
print(f"      Parameter: {model.count_params():,}")

# ── 5. Training Fase 1 (hanya head) ─────────────────────────
print("\n[5/5] Training Fase 1 (head only, 20 epoch)...")
callbacks_1 = [
    EarlyStopping(patience=5, restore_best_weights=True, verbose=1),
    ReduceLROnPlateau(factor=0.5, patience=3, verbose=1)
]

model.fit(
    datagen.flow(X_train_pp, y_train_enc, batch_size=BATCH_SIZE),
    validation_data=(X_val_pp, y_val_enc),
    epochs=20,
    callbacks=callbacks_1,
    verbose=1
)

# ── Fine-tuning: unfreeze layer terakhir VGG16 ───────────────
print("\n[5/5] Fine-tuning (unfreeze 4 layer terakhir VGG16)...")
for layer in base.layers[-4:]:
    layer.trainable = True

model.compile(
    optimizer=Adam(learning_rate=1e-5),  # LR kecil untuk fine-tune
    loss='sparse_categorical_crossentropy',
    metrics=['accuracy']
)

best_path = str(OUTPUT_DIR / "best_adam.h5")
callbacks_2 = [
    ModelCheckpoint(best_path, save_best_only=True, monitor='val_accuracy', verbose=1),
    EarlyStopping(patience=10, restore_best_weights=True, verbose=1),
    ReduceLROnPlateau(factor=0.3, patience=5, verbose=1)
]

history = model.fit(
    datagen.flow(X_train_pp, y_train_enc, batch_size=BATCH_SIZE),
    validation_data=(X_val_pp, y_val_enc),
    epochs=EPOCHS,
    callbacks=callbacks_2,
    verbose=1
)

# ── Simpan model & encoder ────────────────────────────────────
model.save(best_path)
with open(OUTPUT_DIR / "label_encoder.pkl", 'wb') as f:
    pickle.dump(le, f)

# Evaluasi akhir
val_loss, val_acc = model.evaluate(X_val_pp, y_val_enc, verbose=0)
print(f"\n{'='*55}")
print(f"  ✅ Training selesai!")
print(f"  Val Accuracy : {val_acc*100:.1f}%")
print(f"  Val Loss     : {val_loss:.4f}")
print(f"  Model saved  : {best_path}")
print(f"{'='*55}")
