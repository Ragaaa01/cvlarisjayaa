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
        Schema::create('riwayat_deposits', function (Blueprint $table) {
            $table->id('id_riwayat_deposit');

            // Foreign key ke tabel deposits
            $table->foreignId('id_deposit')->constrained('deposits', 'id_deposit');

            $table->enum('jenis_aktivitas', ['top_up', 'potong_denda', 'potong_biaya_admin', 'pengembalian_dana']);
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamp('waktu_aktivitas')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_deposits');
    }
};
