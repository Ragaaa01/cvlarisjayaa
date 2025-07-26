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
        Schema::create('tabungs', function (Blueprint $table) {
            $table->id('id_tabung');
            $table->string('kode_tabung')->unique();

            // Foreign key ke tabel jenis_tabungs
            $table->foreignId('id_jenis_tabung')->constrained('jenis_tabungs', 'id_jenis_tabung');

            // Foreign key ke tabel status_tabungs
            $table->foreignId('id_status_tabung')->constrained('status_tabungs', 'id_status_tabung');

            // Foreign key ke tabel kepemilikans
            $table->foreignId('id_kepemilikan')->constrained('kepemilikans', 'id_kepemilikan');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabungs');
    }
};
