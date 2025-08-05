<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->date('tanggal_lahir')->nullable(); // Izinkan NULL
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('nim', 16)->nullable()->comment('Nomor Induk (numeric only, max 16 digits)');
            $table->string('sekolah')->nullable();
            // $table->year('tahun')->nullable(); 
            $table->timestamps();
            
            
        });
    }

    public function down()
    {
        Schema::dropIfExists('children');
    }
};