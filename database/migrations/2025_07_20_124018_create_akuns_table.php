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
            $table->foreignId('id_role')->constrained('roles', 'id_role')->onDelete('cascade');
            $table->foreignId('id_perorangan')->nullable()->constrained('perorangans', 'id_perorangan')->onDelete('set null');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('status_aktif')->default(true);
            $table->string('remember_token')->nullable();
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
