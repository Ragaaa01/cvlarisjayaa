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
        Schema::create('jenis_tabungs', function (Blueprint $table) {
            $table->id('id_jenis_tabung');
            $table->string('nama_jenis')->unique();
            $table->decimal('harga_pinjam', 15, 2)->default(0)->comment('Harga sewa tabung jika ada');
            $table->decimal('harga_isi_ulang', 15, 2);
            $table->decimal('nilai_deposit', 15, 2)->comment('Jumlah jaminan yang diperlukan untuk meminjam');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_tabungs');
    }
};
