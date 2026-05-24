<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChildController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Child::query();

        if ($search) {
            $query->where('nama', 'like', "%{$search}%")
                ->orWhere('nim', 'like', "%{$search}%");
        }

        $children = $query->paginate(10);
        return view('admin.profile-panti', compact('children'));
    }

    public function create()
    {
        return view('crud.profile-panti-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'nim' => 'nullable|digits_between:1,16|numeric',
            'sekolah' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('children-photos', 'public');
        } elseif ($request->filled('photo_base64')) {
            $image_parts = explode(";base64,", $request->photo_base64);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'children-photos/' . uniqid() . '.jpg';
                Storage::disk('public')->put($fileName, $image_base64);
                $validated['photo'] = $fileName;
            }
        }

        $child = Child::create($validated);
        
        // Sinkronisasi otomatis ke folder dataset CNN
        $this->syncFaceRecognitionDataset($child);

        return redirect()->route('admin.profile.panti')->with('success', 'Data anak berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $child = Child::findOrFail($id);
        return view('crud.profile-panti-edit', compact('child'));
    }

    public function update(Request $request, $id)
    {
        $child = Child::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'nim' => 'nullable|digits_between:1,16|numeric',
            'sekolah' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10000',
        ]);

        if ($request->hasFile('photo')) {
            if ($child->photo) {
                Storage::disk('public')->delete($child->photo);
            }
            $validated['photo'] = $request->file('photo')->store('children-photos', 'public');
        } elseif ($request->filled('photo_base64')) {
            if ($child->photo) {
                Storage::disk('public')->delete($child->photo);
            }
            $image_parts = explode(";base64,", $request->photo_base64);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'children-photos/' . uniqid() . '.jpg';
                Storage::disk('public')->put($fileName, $image_base64);
                $validated['photo'] = $fileName;
            }
        }

        $child->update($validated);
        
        // Sinkronisasi otomatis ke folder dataset CNN
        $this->syncFaceRecognitionDataset($child);
        return redirect()->route('admin.profile.panti')->with('success', 'Data anak berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $child = Child::findOrFail($id);

        // Delete photo if exists
        if ($child->photo) {
            Storage::disk('public')->delete($child->photo);
        }

        // Hapus folder dataset CNN
        $this->removeFaceRecognitionDataset($child);

        $child->delete();
        return redirect()->route('admin.profile.panti')->with('success', 'Data anak berhasil dihapus.');
    }

    public function forTraining()
    {
        return response()->json([
            'success' => true,
            'data' => Child::select('id', 'nama', 'face_encoding_lbph', 'face_encoding_cnn')->get()
        ]);
    }

    /**
     * Memindahkan foto yang diunggah ke folder dataset CNN
     */
    private function syncFaceRecognitionDataset(Child $child)
    {
        if (!$child->photo) return;

        $sourcePath = storage_path('app/public/' . $child->photo);
        if (!file_exists($sourcePath)) return;

        // Bersihkan nama agar aman untuk nama folder
        $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', $child->nama);
        $safeName = trim(preg_replace('/_+/', '_', $safeName), '_');
        
        $datasetDir = base_path("recognition_engine/dataset/{$child->id}_{$safeName}");

        if (!file_exists($datasetDir)) {
            mkdir($datasetDir, 0775, true);
        }

        // Simpan sebagai gambar referensi utama CNN
        $destPath = $datasetDir . '/ref.jpg';
        copy($sourcePath, $destPath);
        
        // Opsional: Jika ada file cache .pkl dari DeepFace, kita hapus agar di-generate ulang
        $cacheFile = base_path("recognition_engine/dataset/representations_vgg_face.pkl");
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Menghapus folder dataset CNN jika anak dihapus
     */
    private function removeFaceRecognitionDataset(Child $child)
    {
        $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', $child->nama);
        $safeName = trim(preg_replace('/_+/', '_', $safeName), '_');
        
        $datasetDir = base_path("recognition_engine/dataset/{$child->id}_{$safeName}");

        if (file_exists($datasetDir)) {
            $files = glob($datasetDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($datasetDir);
        }
        
        // Hapus cache agar sinkron
        $cacheFile = base_path("recognition_engine/dataset/representations_vgg_face.pkl");
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
}