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
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id('id_peminjaman');

            // Foreign key ke tabel akuns
            $table->foreignId('id_akun')->constrained('akuns', 'id_akun');

            // Foreign key ke tagihan (untuk biaya sewa awal jika ada)
            $table->foreignId('id_tagihan')->nullable()->constrained('tagihans', 'id_tagihan');

            $table->timestamp('tanggal_pinjam')->useCurrent();
            $table->timestamp('tanggal_aktivitas_terakhir')->nullable();
            $table->boolean('status_pinjam')->default(true)->comment('true=aktif, false=selesai');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
