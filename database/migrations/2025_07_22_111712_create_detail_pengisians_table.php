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
        Schema::create('detail_pengisians', function (Blueprint $table) {
            $table->id('id_detail_pengisian');

            // Foreign key ke tabel pengisians (kepala transaksi)
            $table->foreignId('id_pengisian')->constrained('pengisians', 'id_pengisian')->onDelete('cascade');

            // Foreign key ke tabel tabungs (bisa null jika pelanggan bawa tabung sendiri)
            $table->foreignId('id_tabung')->nullable()->constrained('tabungs', 'id_tabung');

            // Foreign key ke jenis tabung untuk menentukan harga
            $table->foreignId('id_jenis_tabung')->constrained('jenis_tabungs', 'id_jenis_tabung');

            // Menyimpan harga isi ulang yang berlaku saat transaksi
            $table->decimal('harga_pengisian_saat_itu', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pengisians');
    }
};
