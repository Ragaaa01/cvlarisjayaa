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
        Schema::create('detail_pengembalians', function (Blueprint $table) {
            $table->id('id_detail_pengembalian');
            $table->foreignId('id_pengembalian')->constrained('pengembalians', 'id_pengembalian')->onDelete('cascade');
            $table->foreignId('id_tabung')->constrained('tabungs', 'id_tabung');
            $table->enum('kondisi_tabung', ['baik', 'rusak', 'hilang'])->default('baik');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pengembalians');
    }
};
