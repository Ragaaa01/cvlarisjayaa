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
            $table->foreignId('id_detail_transaksi')->constrained('detail_transaksis', 'id_detail_transaksi')->onDelete('cascade');
            $table->date('tanggal_pinjam');
            $table->date('tanggal_aktivitas_terakhir');
            $table->boolean('status_pinjam')->default(true);
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
