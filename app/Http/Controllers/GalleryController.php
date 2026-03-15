<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::latest()->get();
        $routePrefix = request()->is('admin/*') ? 'admin' : 'pengasuh';
        return view('gallery.index', compact('galleries', 'routePrefix'));
    }

    public function create()
    {
        $routePrefix = request()->is('admin/*') ? 'admin' : 'pengasuh';
        return view('gallery.create', compact('routePrefix'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,webm,ogg|max:20480',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        $imagePath = $request->file('image')->store('galleries', 'public');

        Gallery::create([
            'image' => $imagePath,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        $routePrefix = request()->is('admin/*') ? 'admin' : 'pengasuh';
        return redirect()->route("{$routePrefix}.gallery.index")
                         ->with('success', 'Foto galeri berhasil ditambahkan.');
    }

    public function edit(Gallery $gallery)
    {
        $routePrefix = request()->is('admin/*') ? 'admin' : 'pengasuh';
        return view('gallery.edit', compact('gallery', 'routePrefix'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $request->validate([
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,webm,ogg|max:20480',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($request->hasFile('image')) {
            if (Storage::disk('public')->exists($gallery->image)) {
                Storage::disk('public')->delete($gallery->image);
            }
            $imagePath = $request->file('image')->store('galleries', 'public');
            $gallery->image = $imagePath;
        }

        $gallery->title = $request->title;
        $gallery->description = $request->description;
        $gallery->save();

        $routePrefix = request()->is('admin/*') ? 'admin' : 'pengasuh';
        return redirect()->route("{$routePrefix}.gallery.index")
                         ->with('success', 'Foto galeri berhasil diperbarui.');
    }

    public function destroy(Gallery $gallery)
    {
        if (Storage::disk('public')->exists($gallery->image)) {
            Storage::disk('public')->delete($gallery->image);
        }
        $gallery->delete();

        $routePrefix = request()->is('admin/*') ? 'admin' : 'pengasuh';
        return redirect()->route("{$routePrefix}.gallery.index")
                         ->with('success', 'Foto galeri berhasil dihapus.');
    }
}
