<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Child;
use App\Models\Attendance;
use App\Models\FaceRecognitionLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DummyAttendanceSeeder extends Seeder
{
    public function run()
    {
        $children = Child::all();
        if ($children->isEmpty()) {
            $this->command->info("Tidak ada data anak, aborsi dummy seeder.");
            return;
        }

        // Tanggal awal Februari s/d Awal Maret (misal 10 Maret 2026)
        $start = Carbon::parse('2026-02-01');
        $end = Carbon::parse('2026-03-10');
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            // Hindari hari Sabtu (6) dan Minggu (0)
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($children as $child) {
                // Hapus data yang mungkin ada pada tanggal ini untuk menghindari constraint/duplicate conflicts
                Attendance::where('child_id', $child->id)
                    ->whereRaw('DATE(date) = ?', [$date->format('Y-m-d')])
                    ->delete();

                // 85% peluang hadir via Raspberry Pi
                if (rand(1, 100) <= 85) {
                    $baseCheckInTime = $date->copy()->setTime(rand(6, 7), rand(0, 59), rand(0, 59));
                    $baseCheckOutTime = $date->copy()->setTime(rand(14, 16), rand(0, 59), rand(0, 59));
                    
                    $confidence = rand(780, 990) / 10; // score 78.0 - 99.0%

                    // Tabel Attendances
                    Attendance::create([
                        'child_id' => $child->id,
                        'date' => $date->format('Y-m-d'),
                        'check_in' => $baseCheckInTime,
                        'check_out' => $baseCheckOutTime,
                        'status' => 'hadir',
                        'note' => 'Deteksi otomatis Raspberry Pi',
                        'kamera_id' => 'raspberry_cam_1',
                        'confidence_score' => $confidence,
                        'algoritma' => 'lbph', // Real algoritma dari raspberry
                    ]);

                    // Tabel Face_Recognition_Logs (Check In)
                    FaceRecognitionLog::insert([
                        'id' => Str::uuid()->toString(),
                        'child_id' => $child->id,
                        'confidence_score' => $confidence,
                        'algoritma' => 'lbph',
                        'status' => 'check_in',
                        'kamera_id' => 'raspberry_cam_1',
                        'waktu_deteksi' => $baseCheckInTime,
                        'created_at' => $baseCheckInTime,
                        'updated_at' => $baseCheckInTime,
                    ]);

                    // Tabel Face_Recognition_Logs (Check Out)
                    FaceRecognitionLog::insert([
                        'id' => Str::uuid()->toString(),
                        'child_id' => $child->id,
                        'confidence_score' => $confidence + ((rand(-20, 20))/10),
                        'algoritma' => 'lbph',
                        'status' => 'check_out',
                        'kamera_id' => 'raspberry_cam_1',
                        'waktu_deteksi' => $baseCheckOutTime,
                        'created_at' => $baseCheckOutTime,
                        'updated_at' => $baseCheckOutTime,
                    ]);
                } else {
                    // 15% peluang absen secara manual (Di-input oleh pengasuh)
                    $statusArr = ['sakit', 'izin', 'alpa'];
                    $randStatus = $statusArr[array_rand($statusArr)];
                    
                    Attendance::create([
                        'child_id' => $child->id,
                        'date' => $date->format('Y-m-d'),
                        'check_in' => null,
                        'check_out' => null,
                        'status' => $randStatus,
                        'note' => 'Data input manual',
                        'kamera_id' => 'manual',
                        'confidence_score' => null,
                        'algoritma' => 'manual',
                    ]);
                }
            }
        }
        $this->command->info("Dummy absensi bulan Februari - Maret selesai di-generate!");
    }
}
