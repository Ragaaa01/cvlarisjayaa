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
        Schema::create('akuns', function (Blueprint $table) {
            $table->id('id_akun');

            // Foreign key ke tabel roles
            $table->foreignId('id_role')->constrained('roles', 'id_role');

            // Foreign key ke tabel orangs (nullable)
            $table->foreignId('id_orang')->nullable()->constrained('orangs', 'id_orang')->onDelete('cascade');

            // Kolom untuk login, bisa kosong untuk pelanggan non-aplikasi
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();

            // Status untuk aktivasi akun oleh admin
            $table->boolean('status_aktif')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akuns');
    }
};
