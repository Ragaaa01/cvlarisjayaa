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
        Schema::create('dendas', function (Blueprint $table) {
            $table->id('id_denda');

            // Foreign key ke tabel peminjamans (penyebab denda)
            $table->foreignId('id_peminjaman')->constrained('peminjamans', 'id_peminjaman');

            // Foreign key ke tabel akuns (yang dikenai denda)
            $table->foreignId('id_akun')->constrained('akuns', 'id_akun');

            $table->enum('jenis_denda', ['rusak', 'hilang', 'inaktivitas']);
            $table->decimal('jumlah_denda', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dendas');
    }
};
