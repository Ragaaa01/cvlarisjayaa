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
    $table->unsignedBigInteger('id_akun');
    $table->unsignedBigInteger('id_template');
    $table->string('judul')->nullable(); // Hapus ->after('id_template')
    $table->text('isi')->nullable();     // Hapus ->after('judul')
    $table->timestamp('tanggal_terjadwal');
    $table->boolean('status_baca')->default(0);
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
