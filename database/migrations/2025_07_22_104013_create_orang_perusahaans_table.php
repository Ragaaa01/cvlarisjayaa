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
        Schema::create('orang_perusahaans', function (Blueprint $table) {
            $table->id('id_orang_perusahaan');

            // Foreign key ke tabel orangs
            $table->foreignId('id_orang')->constrained('orangs', 'id_orang')->onDelete('cascade');

            // Foreign key ke tabel perusahaans
            $table->foreignId('id_perusahaan')->constrained('perusahaans', 'id_perusahaan')->onDelete('cascade');

            // Status atau peran orang tersebut di perusahaan
            $table->string('status')->comment('Contoh: Pemilik, Karyawan, PIC');

            $table->timestamps();

            // Menambahkan unique constraint untuk mencegah duplikasi relasi
            $table->unique(['id_orang', 'id_perusahaan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orang_perusahaans');
    }
};
