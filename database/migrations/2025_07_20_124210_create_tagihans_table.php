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
               $table->foreignId('id_perorangan')->constrained('perorangans', 'id_perorangan')->onDelete('cascade');
               $table->decimal('total_tagihan', 10, 2);
               $table->decimal('jumlah_dibayar', 10, 2)->default(0);
               $table->decimal('sisa', 10, 2)->default(0);
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
