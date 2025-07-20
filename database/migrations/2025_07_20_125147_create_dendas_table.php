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
        Schema::create('dendas', function (Blueprint $table) {
            $table->id('id_denda');
            $table->foreignId('id_peminjaman')->constrained('peminjamans', 'id_peminjaman')->onDelete('cascade');
            $table->foreignId('id_tagihan')->constrained('tagihans', 'id_tagihan')->onDelete('cascade');
            $table->decimal('jumlah_denda', 10, 2);
            $table->string('periode_denda');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dendas');
    }
};
