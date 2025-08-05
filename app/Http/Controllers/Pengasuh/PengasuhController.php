<?php

namespace App\Http\Controllers\Pengasuh;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Panti;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Tambahkan untuk menghapus file foto
use Illuminate\Support\Facades\Log; // Tambahkan untuk debugging
use App\Models\User; // Tambahkan untuk menghitung total pengasuh

class PengasuhController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
    
        $stats = [
            'total_children' => Child::count(),
            'total_pengasuh' => User::where('role', 'pengasuh')->count(),
            'total_laki' => Child::where('jenis_kelamin', 'L')->count(),
            'total_perempuan' => Child::where('jenis_kelamin', 'P')->count(),
        ];
    
        $recent_activities = Attendance::with('child')
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Mengakses Dashboard Pengasuh',
            'status' => 'Berhasil'
        ]);
    
        return view('pengasuh.dashboard', compact('stats', 'recent_activities'));
    }

    public function cctv()
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Mengakses CCTV',
            'status' => 'Berhasil'
        ]);
        return view('cctv.cctv');
    }

    public function profilePanti(Request $request)
    {
        $search = $request->input('search');
        $query = Child::query();

        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $children = $query->paginate(10);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Melihat Profil Panti',
            'status' => 'Berhasil'
        ]);

        return view('admin.profile-panti', compact('children'));
    }

    public function create()
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Membuka Form Tambah Data Anak',
            'status' => 'Berhasil'
        ]);

        return view('crud.profile-panti-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'nim' => 'nullable|digits:16|numeric',
            'sekolah' => 'nullable|string|max:255',
            'panti_id' => 'nullable|exists:pantis,id',
            'photo' => 'nullable|image|max:10240', // 10MB
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('children-photos', 'public');
        }

        $child = Child::create($validated);
        \Log::info('Data disimpan: ', $child->toArray());

        ActivityLog::create([
            'user_id' => \Auth::id(),
            'activity' => 'Menambahkan Data Anak: ' . $validated['nama'],
            'status' => 'Berhasil'
        ]);

        return redirect()->route('pengasuh.profile.panti')->with('success', 'Data anak berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'nim' => 'nullable|digits:16|numeric',
            'sekolah' => 'nullable|string|max:255',
            'panti_id' => 'nullable|exists:pantis,id',
            'photo' => 'nullable|image|max:10240', // 10MB
        ]);

        $child = \App\Models\Child::findOrFail($id);

        $data = array_filter($validated, function($v) { return $v !== null && $v !== ''; });

        if ($request->hasFile('photo')) {
            if ($child->photo) {
                \Storage::disk('public')->delete($child->photo);
            }
            $data['photo'] = $request->file('photo')->store('children-photos', 'public');
        }

        $child->update($data);
        \Log::info('Data diperbarui: ', $child->toArray());

        \App\Models\ActivityLog::create([
            'user_id' => \Auth::id(),
            'activity' => 'Mengedit Data Anak: ' . $child->nama,
            'status' => 'Berhasil'
        ]);

        return redirect()->route('pengasuh.profile.panti')->with('success', 'Data anak berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $child = Child::findOrFail($id);
        $nama = $child->nama; // Simpan nama sebelum dihapus
        if ($child->photo) {
            Storage::disk('public')->delete($child->photo); // Hapus foto jika ada
        }
        $child->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Menghapus Data Anak: ' . $nama,
            'status' => 'Berhasil'
        ]);

        return redirect()->route('pengasuh.profile.panti')->with('success', 'Data anak berhasil dihapus.');
    }

    public function attendance()
{
    $today = Carbon::today();
    $attendances = Attendance::with('child')
        ->whereDate('date', $today) // Ganti dari 'created_at' ke 'date'
        ->orderBy('date', 'desc')
        ->get();

    $children = Child::all();

    ActivityLog::create([
        'user_id' => Auth::id(),
        'activity' => 'Melihat Data Kehadiran Anak',
        'status' => 'Berhasil'
    ]);

    return view('attendance.attendance', compact('attendances', 'children')); // Gunakan view universal
}

public function checkIn(Request $request)
{
    $request->validate([
        'child_id' => 'required|exists:children,id',
        'date' => 'required|date', // Tambahkan validasi date
        'status' => 'required|in:hadir,sakit,izin',
        'note' => 'nullable|string'
    ]);

    $existing = Attendance::where('child_id', $request->child_id)
        ->whereDate('date', $request->date)
        ->first();

    if ($existing) {
        return back()->with('error', 'Anak ini sudah melakukan check-in untuk tanggal tersebut');
    }

    $attendance = Attendance::create([
        'child_id' => $request->child_id,
        'date' => $request->date,
        'check_in' => Carbon::now(),
        'status' => $request->status,
        'note' => $request->note,
    ]);

    ActivityLog::create([
        'user_id' => Auth::id(),
        'activity' => 'Check-in anak: ' . optional($attendance->child)->nama,
        'status' => 'Berhasil'
    ]);

    return back()->with('success', 'Check-in berhasil dicatat');
}

public function checkOut($id)
{
    $attendance = Attendance::findOrFail($id);

    if ($attendance->check_out) {
        return back()->with('error', 'Anak ini sudah check-out');
    }

    $attendance->update([
        'check_out' => Carbon::now()
    ]);

    ActivityLog::create([
        'user_id' => Auth::id(),
        'activity' => 'Check-out anak: ' . $attendance->child->nama,
        'status' => 'Berhasil'
    ]);

    return back()->with('success', 'Check-out berhasil dicatat');
}

// Tambahkan manualAttendance jika diperlukan
public function manualAttendance(Request $request)
{
    $request->validate([
        'child_id' => 'required|exists:children,id',
        'date' => 'required|date',
        'check_in' => 'required|date_format:H:i',
        'check_out' => 'nullable|date_format:H:i',
        'status' => 'required|in:hadir,izin,sakit,alpha',
        'note' => 'nullable|string'
    ]);

    $checkIn = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->check_in);
    $checkOut = $request->check_out
        ? Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->check_out)
        : null;

    $attendance = Attendance::create([
        'child_id' => $request->child_id,
        'date' => $request->date,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'status' => $request->status,
        'note' => $request->note
    ]);

    ActivityLog::create([
        'user_id' => Auth::id(),
        'activity' => 'Input manual kehadiran untuk: ' . optional($attendance->child)->nama,
        'status' => 'Berhasil'
    ]);

    return back()->with('success', 'Data kehadiran manual berhasil disimpan');
}

public function updateAttendance(Request $request)
{
    $request->validate([
        'id' => 'required|exists:attendances,id',
        'status' => 'required|in:hadir,sakit,izin',
        'note' => 'nullable|string'
    ]);

    $attendance = Attendance::findOrFail($request->id);
    $attendance->update([
        'status' => $request->status,
        'note' => $request->note
    ]);

    ActivityLog::create([
        'user_id' => Auth::id(),
        'activity' => 'Mengupdate kehadiran untuk: ' . $attendance->child->nama,
        'status' => 'Berhasil'
    ]);

    return redirect()->back()
        ->with('success', 'Data kehadiran berhasil diperbarui');
}

    public function edit($id)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Membuka Form Edit Data Anak',
            'status' => 'Berhasil'
        ]);

        $child = Child::findOrFail($id);
        return view('crud.profile-panti-edit', compact('child'));
    }
}