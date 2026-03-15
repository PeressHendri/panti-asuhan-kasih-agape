<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('face_recognition_logs', function (Blueprint $table) {
            $table->index('waktu_deteksi');
            $table->index('status');
        });

        Schema::table('children', function (Blueprint $table) {
            $table->index('nama'); // Sering digunakan untuk pencarian
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->index('created_at'); // Sering digunakan untuk sorting 'latest()'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('face_recognition_logs', function (Blueprint $table) {
            $table->dropIndex(['waktu_deteksi']);
            $table->dropIndex(['status']);
        });

        Schema::table('children', function (Blueprint $table) {
            $table->dropIndex(['nama']);
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
