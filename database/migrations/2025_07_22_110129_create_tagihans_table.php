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
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id('id_tagihan');

            // Foreign key ke tabel akuns
            $table->foreignId('id_akun')->constrained('akuns', 'id_akun');

            $table->decimal('total_tagihan', 15, 2);
            $table->decimal('jumlah_dibayar', 15, 2)->default(0);
            $table->decimal('sisa', 15, 2);
            $table->enum('status_tagihan', ['lunas', 'belum_lunas'])->default('belum_lunas');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
