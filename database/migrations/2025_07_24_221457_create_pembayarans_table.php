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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id('id_pembayaran');

            // Foreign key ke tabel orangs
            $table->foreignId('id_orang')->constrained('orangs', 'id_orang');

            $table->decimal('total_transaksi', 15, 2)->comment('Total utang saat pembayaran dilakukan');
            $table->decimal('jumlah_pembayaran', 15, 2);
            $table->string('metode_pembayaran');
            $table->string('nomor_referensi')->nullable()->comment('ID unik dari payment gateway');
            $table->date('tanggal_pembayaran');
            $table->time('waktu_pembayaran');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
