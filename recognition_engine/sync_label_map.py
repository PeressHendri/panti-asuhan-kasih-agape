"""
====================================================
sync_label_map.py
Script untuk menyinkronisasi label_map.pkl LBPH
dengan child_id yang ada di Database Laravel (MySQL).
====================================================
Jalankan sekali sebelum menjalankan main_recognition.py:
  python sync_label_map.py

Output: label_map.pkl yang sudah di-update
"""

import pickle
import os
import requests

# ========================================
# KONFIGURASI API
# ========================================
API_URL = "https://pantiasuhankasihagape.id/api/children/for-training"
API_TOKEN = "kasihagape2025secret"

BASE_DIR         = os.path.dirname(os.path.abspath(__file__))
LABEL_MAP_FILE   = os.path.join(BASE_DIR, 'models', 'lbph', 'label_map.pkl')

def normalize(text: str) -> str:
    """Hilangkan underscore, angka di ujung, huruf kecil semua untuk perbandingan."""
    parts = text.replace('_', ' ').split()
    clean = [p for p in parts if not p.isdigit()]
    return ' '.join(clean[:3]).lower()

def sync():
    if not os.path.exists(LABEL_MAP_FILE):
        print(f"❌ File tidak ditemukan: {LABEL_MAP_FILE}")
        return

    # Baca label map yang lama (PKL format)
    with open(LABEL_MAP_FILE, 'rb') as f:
        raw_map = pickle.load(f)

    print("Mencoba mengambil data dari API...")
    headers = {
        "Authorization": f"Bearer {API_TOKEN}",
        "Accept": "application/json"
    }
    
    try:
        response = requests.get(API_URL, headers=headers, timeout=10)
        response.raise_for_status()
        data = response.json()
        children = data.get('data', [])
    except Exception as e:
        print(f"❌ Gagal mengambil data dari API: {e}")
        return

    if not children:
        print("⚠️  Data children kosong dari API!")
        return

    print(f"\n📋 Ditemukan {len(children)} anak di API & {len(raw_map)} label di label_map.pkl\n")

    output_data = {}

    for lbph_index, label_data in raw_map.items():
        # label_data bisa berupa string nama (versi lama) atau dictionary (versi baru)
        if isinstance(label_data, dict):
            label_name = label_data.get('nama', str(label_data))
        else:
            label_name = str(label_data)
            
        label_norm  = normalize(label_name)

        matched = None
        best_score = 0

        for child in children:
            child_norm  = normalize(child['nama'])
            label_words = label_norm.split()
            child_words = child_norm.split()
            score = sum(1 for a, b in zip(label_words, child_words) if a == b)

            if score > best_score:
                best_score = score
                matched = child

        if matched and best_score >= 1:
            # Simpan ID dari DB dan Nama dari DB
            output_data[lbph_index] = {
                "id": matched['id'],
                "nama": matched['nama']
            }
            print(f"  ✅ [{lbph_index:>2}] '{label_name:40s}' → child_id={matched['id']:>4}  ({matched['nama']})")
        else:
            # Jika tidak ketemu, biarkan seperti semula
            output_data[lbph_index] = label_data
            print(f"  ❌ [{lbph_index:>2}] '{label_name}' → TIDAK DITEMUKAN di database! (Dipertahankan)")

    # Timpa file label_map.pkl lama dengan yang baru disync
    with open(LABEL_MAP_FILE, 'wb') as f:
        pickle.dump(output_data, f)

    print(f"\n✅ Sinkronisasi selesai. File {LABEL_MAP_FILE} telah di-update.")
    print("Sistem siap dijalankan!\n")

if __name__ == '__main__':
    sync()
