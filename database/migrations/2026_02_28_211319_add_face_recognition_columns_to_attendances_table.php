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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('kamera_id', 50)->default('pintu_masuk_utama')->after('note');
            $table->float('confidence_score')->nullable()->after('kamera_id');
            $table->enum('algoritma', ['lbph', 'cnn', 'manual'])->default('manual')->after('confidence_score');
            $table->string('foto_capture_path')->nullable()->after('algoritma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['kamera_id', 'confidence_score', 'algoritma', 'foto_capture_path']);
        });
    }
};
