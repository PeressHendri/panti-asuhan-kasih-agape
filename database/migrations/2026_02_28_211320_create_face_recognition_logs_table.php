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
        Schema::create('face_recognition_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('child_id')->nullable()->constrained('children')->nullOnDelete();
            $table->string('foto_capture_path')->nullable();     // foto saat deteksi
            $table->float('confidence_score')->nullable();       // nilai LBPH/CNN
            $table->enum('algoritma', ['lbph', 'cnn'])->default('lbph');
            $table->enum('status', ['check_in', 'check_out', 'tidak_dikenal']);
            $table->string('kamera_id', 50)->default('pintu_masuk_utama');
            $table->timestamp('waktu_deteksi')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_recognition_logs');
    }
};
