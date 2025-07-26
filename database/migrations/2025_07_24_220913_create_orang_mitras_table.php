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
        Schema::create('orang_mitras', function (Blueprint $table) {
            $table->id('id_orang_mitra');

            // Foreign key ke tabel orangs
            $table->foreignId('id_orang')->constrained('orangs', 'id_orang')->onDelete('cascade');

            // Foreign key ke tabel mitras
            $table->foreignId('id_mitra')->constrained('mitras', 'id_mitra')->onDelete('cascade');

            $table->boolean('status_valid')->default(false);

            $table->timestamps();

            // Menambahkan unique constraint untuk mencegah duplikasi relasi
            $table->unique(['id_orang', 'id_mitra']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orang_mitras');
    }
};
