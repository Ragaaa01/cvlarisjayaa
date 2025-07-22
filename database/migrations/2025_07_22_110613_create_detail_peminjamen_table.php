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
        Schema::create('detail_peminjamans', function (Blueprint $table) {
            $table->id('id_detail_peminjaman');

            // Foreign key ke tabel peminjamans (kepala transaksi)
            $table->foreignId('id_peminjaman')->constrained('peminjamans', 'id_peminjaman')->onDelete('cascade');

            // Foreign key ke tabel tabungs (item spesifik yang dipinjam)
            $table->foreignId('id_tabung')->constrained('tabungs', 'id_tabung');

            // Menyimpan harga sewa yang berlaku saat transaksi terjadi
            $table->decimal('harga_pinjam_saat_itu', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_peminjamen');
    }
};
