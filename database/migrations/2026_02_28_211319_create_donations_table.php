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
        Schema::create('donations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_donatur', 100);
            $table->string('email', 100)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->enum('jenis_donasi', ['uang', 'barang', 'sponsor_anak']);
            $table->decimal('jumlah', 15, 2)->nullable();       // untuk donasi uang
            $table->text('keterangan')->nullable();              // untuk donasi barang
            $table->string('nomor_resi', 100)->nullable();       // nomor transfer
            $table->string('bukti_transfer_path')->nullable();   // foto bukti transfer
            $table->foreignId('child_id')->nullable()->constrained('children')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'konfirmasi', 'ditolak'])->default('pending');
            $table->date('tanggal');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
