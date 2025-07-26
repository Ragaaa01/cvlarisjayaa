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
        Schema::create('kepemilikans', function (Blueprint $table) {
            $table->id('id_kepemilikan');
            $table->string('keterangan_kepemilikan')->unique(); // Contoh: milik_laris_jaya_gas, milik_pelanggan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kepemilikans');
    }
};
