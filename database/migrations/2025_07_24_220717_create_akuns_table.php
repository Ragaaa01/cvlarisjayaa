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
            $table->id('id_akuns');

            // Foreign key ke tabel roles
            $table->foreignId('id_role')->constrained('roles', 'id_role');

            // Foreign key ke tabel orangs
            $table->foreignId('id_orang')->constrained('orangs', 'id_orang');

            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->boolean('status_aktif')->default(true);

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
