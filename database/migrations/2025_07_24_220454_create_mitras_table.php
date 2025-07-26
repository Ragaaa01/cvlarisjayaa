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
        Schema::create('mitras', function (Blueprint $table) {
            $table->id('id_mitra');
            $table->string('nama_mitra');

            // Foreign key ke tabel kelurahans (bisa null jika alamat belum lengkap)
            $table->foreignId('id_kelurahan')->nullable()->constrained('kelurahans', 'id_kelurahan');

            $table->text('alamat_mitra')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitras');
    }
};
