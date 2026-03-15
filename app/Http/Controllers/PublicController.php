<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicController extends Controller
{
    public function index()
    {
        // Use Cache to store the count for 60 minutes
        $childCount = \Illuminate\Support\Facades\Cache::remember('child_count', 3600, function () {
            return \App\Models\Child::count();
        });

        $galleries = \App\Models\Gallery::select('id', 'title', 'description', 'image')
            ->latest()
            ->take(9)
            ->get();
        
        return view('welcome', compact('childCount', 'galleries'));
    }

    public function profile()
    {
        return redirect('/#about');
    }

    public function donasi()
    {
        return view('donasi');
    }

    public function storeDonasi(Request $request)
    {
        $request->validate([
            'nama_donatur' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'telepon' => 'nullable|string|max:20',
            'jenis_donasi' => 'required|in:uang,barang,sponsor_anak',
            'jumlah' => 'nullable|numeric|required_if:jenis_donasi,uang',
            'keterangan' => 'nullable|string',
            'nomor_resi' => 'nullable|string|max:100',
            'bukti_transfer' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'tanggal' => 'required|date',
        ]);

        $data = $request->except('bukti_transfer');
        $data['id'] = (string) Str::uuid();
        $data['status'] = 'pending';

        if ($request->hasFile('bukti_transfer')) {
            $data['bukti_transfer_path'] = $request->file('bukti_transfer')->store('donations', 'public');
        }

        \App\Models\Donation::create($data);

        return back()->with('success', 'Terima kasih atas donasi Anda! Kami akan segera memverifikasinya.');
    }
}