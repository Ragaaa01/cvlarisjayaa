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
            $table->string('nama_jenis');
            $table->decimal('harga_sewa', 10, 2);
            $table->decimal('harga_isi_gas', 10, 2);
            $table->decimal('nilai_deposit', 10, 2);
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
