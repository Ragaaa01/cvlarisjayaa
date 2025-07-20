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
               $table->foreignId('id_akun')->nullable()->constrained('akuns', 'id_akun')->onDelete('set null');
               $table->foreignId('id_perorangan')->constrained('perorangans', 'id_perorangan')->onDelete('cascade');
               $table->foreignId('id_perusahaan')->nullable()->constrained('perusahaans', 'id_perusahaan')->onDelete('set null');
               $table->foreignId('id_tagihan')->constrained('tagihans', 'id_tagihan')->onDelete('cascade');
               $table->decimal('total_transaksi', 10, 2);
               $table->foreignId('id_status_transaksi')->constrained('status_transaksis', 'id_status_transaksi')->onDelete('cascade');
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
