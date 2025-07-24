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
        Schema::create('pengembalians', function (Blueprint $table) {
            $table->id('id_pengembalian');

            // Foreign keys
            $table->foreignId('id_tabung')->constrained('tabungs', 'id_tabung');
            $table->foreignId('id_transaksi_detail')->constrained('transaksi_details', 'id_transaksi_detail');
            $table->foreignId('id_status_tabung')->comment('Status tabung setelah dikembalikan')->constrained('status_tabungs', 'id_status_tabung');

            // Data waktu
            $table->timestamp('tanggal_pinjam');
            $table->time('waktu_pinjam');
            $table->timestamp('tanggal_pengembalian')->nullable();
            $table->time('waktu_pengembalian')->nullable();

            // Data kalkulasi denda
            $table->integer('jumlah_keterlambatan_bulan')->default(0);
            $table->decimal('total_denda', 15, 2)->default(0);
            $table->decimal('denda_kondisi_tabung', 15, 2)->default(0);

            // Data kalkulasi deposit
            $table->decimal('deposit', 15, 2)->comment('Nilai deposit awal saat peminjaman');
            $table->decimal('sisa_deposit', 15, 2)->comment('Sisa deposit yang dikembalikan');
            $table->decimal('bayar_tagihan', 15, 2)->default(0)->comment('Kekurangan yang harus dibayar pelanggan');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembalians');
    }
};
