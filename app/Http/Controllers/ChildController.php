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
        dd($children->toArray());
        return view('admin.profile-panti', compact('children'));
    }

    public function create()
    {
        return view('admin.profile-panti-create');
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
        }

        Child::create($validated);
        
        return redirect()->route('admin.profile.panti')->with('success', 'Data anak berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $child = Child::findOrFail($id);
        return view('admin.profile-panti-edit', compact('child'));
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
            // Delete old photo if exists
            if ($child->photo) {
                Storage::disk('public')->delete($child->photo);
            }
            $validated['photo'] = $request->file('photo')->store('children-photos', 'public');
        }

        $child->update($validated);
        return redirect()->route('admin.profile.panti')->with('success', 'Data anak berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $child = Child::findOrFail($id);
        
        // Delete photo if exists
        if ($child->photo) {
            Storage::disk('public')->delete($child->photo);
        }
        
        $child->delete();
        return redirect()->route('admin.profile.panti')->with('success', 'Data anak berhasil dihapus.');
    }
}