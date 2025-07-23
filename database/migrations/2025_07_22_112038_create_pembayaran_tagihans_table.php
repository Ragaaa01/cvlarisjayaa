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
        Schema::create('pembayaran_tagihans', function (Blueprint $table) {
            $table->id('id_pembayaran_tagihan');

            // Foreign key ke tabel tagihans
            $table->foreignId('id_tagihan')->constrained('tagihans', 'id_tagihan');

            $table->decimal('jumlah_dibayar', 15, 2);
            $table->timestamp('tanggal_bayar')->useCurrent();
            $table->string('metode_pembayaran');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_tagihans');
    }
};
