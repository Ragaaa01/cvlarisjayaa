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
        Schema::create('pengisians', function (Blueprint $table) {
            $table->id('id_pengisian');

            // Foreign key ke tabel akuns
            $table->foreignId('id_akun')->constrained('akuns', 'id_akun');

            // Foreign key ke tagihan yang wajib dilunasi
            $table->foreignId('id_tagihan')->constrained('tagihans', 'id_tagihan');

            $table->decimal('total_biaya', 15, 2);
            $table->timestamp('waktu_transaksi')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengisians');
    }
};
