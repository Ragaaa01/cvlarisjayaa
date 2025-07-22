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

            // Foreign key ke tabel akuns, harus unik karena 1 akun hanya punya 1 dompet deposit
            $table->foreignId('id_akun')->unique()->constrained('akuns', 'id_akun')->onDelete('cascade');

            $table->decimal('saldo', 15, 2)->default(0);
            $table->enum('status_deposit', ['aktif', 'dibekukan'])->default('aktif');

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
