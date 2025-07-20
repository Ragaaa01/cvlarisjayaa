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
        Schema::create('deposits', function (Blueprint $table) {
               $table->id('id_deposit');
               $table->foreignId('id_peminjaman')->constrained('peminjamans', 'id_peminjaman')->onDelete('cascade');
               $table->foreignId('id_perorangan')->constrained('perorangans', 'id_perorangan')->onDelete('cascade');
               $table->decimal('jumlah_deposit', 10, 2);
               $table->enum('status_deposit', ['aktif', 'dikembalikan'])->default('aktif');
               $table->timestamps();
           });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
