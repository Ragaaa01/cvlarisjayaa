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
            $table->foreignId('id_tagihan')->constrained('tagihans', 'id_tagihan')->onDelete('cascade');
            $table->decimal('jumlah_dibayar', 10, 2);
            $table->date('tanggal_bayar');
            $table->enum('metode_pembayaran', ['tunai', 'transfer', 'deposit']);
            $table->text('keterangan')->nullable();
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
