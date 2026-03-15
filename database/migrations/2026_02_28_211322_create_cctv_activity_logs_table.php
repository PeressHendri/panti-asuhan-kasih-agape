<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cctv_activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kamera_id', 50);
            $table->foreign('kamera_id')->references('kamera_id')->on('cctv_cameras')->onDelete('cascade');
            $table->enum('jenis_aktivitas', [
                'motion_detected',    // gerakan terdeteksi
                'object_tracked',     // objek dilacak
                'camera_online',      // kamera menyala
                'camera_offline'      // kamera mati
            ]);
            $table->text('keterangan')->nullable();
            $table->string('snapshot_path')->nullable();   // foto snapshot saat aktivitas
            $table->timestamp('waktu')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cctv_activity_logs');
    }
};
