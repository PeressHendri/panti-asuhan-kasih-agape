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
        Schema::create('cctv_cameras', function (Blueprint $table) {
            $table->id();
            $table->string('kamera_id', 50)->unique();    // ruang_belajar, halaman, dll
            $table->string('nama', 100);                  // Ruang Belajar, Halaman, dll
            $table->string('rtsp_url')->nullable();        // URL stream RTSP
            $table->string('hls_url')->nullable();         // URL stream HLS untuk web
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(false);  // diupdate dari Raspberry Pi
            $table->timestamp('last_ping')->nullable();    // kapan terakhir online
            $table->string('lokasi', 100)->nullable();     // deskripsi lokasi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cctv_cameras');
    }
};
