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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id('id_transaksi');

            // Foreign key ke tabel orangs
            $table->foreignId('id_orang')->constrained('orangs', 'id_orang');

            $table->decimal('total_transaksi', 15, 2);
            $table->boolean('status_valid')->default(true)->comment('true = valid, false = batal');
            $table->date('tanggal_transaksi');
            $table->time('waktu_transaksi');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
