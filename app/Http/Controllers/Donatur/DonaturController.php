<?php

namespace App\Http\Controllers\Donatur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DonaturController extends Controller
{
    public function dashboard() {
        $statCards = [
            [
                'color' => '#4CAF50',
                'icon' => 'fa-users',
                'count' => \App\Models\Child::count(),
                'title' => 'Total Anak',
            ],
            [
                'color' => '#00BCD4', // Warna cyan
                'icon' => 'fa-mars',  // Ikon untuk laki-laki
                'count' => \App\Models\Child::where('jenis_kelamin', 'L')->count(),
                'title' => 'Total Laki-laki',
            ],
            [
                'color' => '#E91E63', // Warna pink
                'icon' => 'fa-venus', // Ikon untuk perempuan
                'count' => \App\Models\Child::where('jenis_kelamin', 'P')->count(),
                'title' => 'Total Perempuan',
            ],
            [
                'color' => '#2196F3',
                'icon' => 'fa-calendar-check', // Ikon lebih spesifik
                'count' => \App\Models\Attendance::whereDate('check_in', now())->count(), // Kehadiran hari ini
                'title' => 'Hadir Hari Ini', // Judul lebih spesifik
            ],
        ];
        return view('donatur.dashboard', compact('statCards'));
    }
    public function profilePanti() {
        $children = \App\Models\Child::all();
        return view('donatur.profile-panti', compact('children'));
    }
    public function attendance() {
        $attendances = \App\Models\Attendance::with('child')->orderBy('date', 'desc')->paginate(10);
        $children = \App\Models\Child::all();
        return view('donatur.attendance', compact('attendances', 'children'));
    }
    public function cctv() {
        return view('cctv.cctv');
    }

    // Halaman data pengasuh khusus donatur
    public function pengasuh() {
        $pengasuhList = \App\Models\User::where('role', 'pengasuh')->get();
        return view('donatur.pengasuh', compact('pengasuhList'));
    }
}
