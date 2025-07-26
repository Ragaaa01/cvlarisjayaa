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
        Schema::create('transaksi_details', function (Blueprint $table) {
            $table->id('id_transaksi_detail');

            // Foreign key ke tabel transaksis (kepala transaksi)
            $table->foreignId('id_transaksi')->constrained('transaksis', 'id_transaksi')->onDelete('cascade');

            // Foreign key ke tabel tabungs (bisa null)
            $table->foreignId('id_tabung')->nullable()->constrained('tabungs', 'id_tabung');

            // Foreign key ke tabel jenis_transaksi_details
            $table->foreignId('id_jenis_transaksi_detail')->constrained('jenis_transaksi_details', 'id_jenis_transaksi_detail');

            $table->decimal('harga', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_details');
    }
};
