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
        Schema::table('children', function (Blueprint $table) {
            // Hapus kolom nim yang tidak relevan
            $table->dropColumn('nim');

            // Tambah kolom yang dibutuhkan
            $table->string('asal_daerah', 100)->nullable()->after('jenis_kelamin');
            $table->date('tanggal_masuk')->nullable()->after('asal_daerah');
            $table->text('keterangan')->nullable()->after('tanggal_masuk');
            $table->boolean('status_sponsor')->default(false)->after('keterangan');
            $table->string('nama_sponsor', 100)->nullable()->after('status_sponsor');

            // Tambah kolom face encoding untuk Raspberry Pi
            $table->longText('face_encoding_lbph')->nullable()->after('nama_sponsor');
            $table->longText('face_encoding_cnn')->nullable()->after('face_encoding_lbph');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->string('nim', 16)->nullable();
            $table->dropColumn([
                'asal_daerah',
                'tanggal_masuk',
                'keterangan',
                'status_sponsor',
                'nama_sponsor',
                'face_encoding_lbph',
                'face_encoding_cnn'
            ]);
        });
    }
};
