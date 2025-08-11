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
        Schema::create('orangs', function (Blueprint $table) {
            $table->id('id_orang');
            $table->string('nama_lengkap');
            $table->string('nik')->unique()->nullable();
            $table->string('no_telepon')->unique()->nullable();

            // Foreign key ke tabel kelurahans (bisa null jika alamat belum lengkap)
            $table->foreignId('id_kelurahan')->nullable()->constrained('kelurahans', 'id_kelurahan');

            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orangs');
    }
};
