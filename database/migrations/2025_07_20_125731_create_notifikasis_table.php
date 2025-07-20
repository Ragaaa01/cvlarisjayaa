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
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->foreignId('id_tagihan')->constrained('tagihans', 'id_tagihan')->onDelete('cascade');
            $table->foreignId('id_notifikasi_template')->constrained('notifikasi_templates', 'id_notifikasi_template')->onDelete('cascade');
            $table->date('tanggal_terjadwal');
            $table->boolean('status_baca')->default(false);
            $table->timestamp('waktu_dikirim')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
