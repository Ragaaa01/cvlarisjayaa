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
        Schema::create('detail_transaksis', function (Blueprint $table) {
            $table->id('id_detail_transaksi');
            $table->foreignId('id_transaksi')->constrained('transaksis', 'id_transaksi')->onDelete('cascade');
            $table->foreignId('id_tabung')->nullable()->constrained('tabungs', 'id_tabung')->onDelete('set null');
            $table->foreignId('id_jenis_transaksi')->constrained('jenis_transaksis', 'id_jenis_transaksi')->onDelete('cascade');
            $table->decimal('harga', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksis');
    }
};
