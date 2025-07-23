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

            // Foreign key ke akun yang menerima notifikasi
            $table->foreignId('id_akun')->constrained('akuns', 'id_akun')->onDelete('cascade');
            $table->foreignId('id_peminjaman')->nullable()->constrained('peminjamans', 'id_peminjaman')->onDelete('cascade');

            // Foreign key ke tagihan (jika notifikasi terkait tagihan)
            $table->foreignId('id_tagihan')->nullable()->constrained('tagihans', 'id_tagihan')->onDelete('set null');

            // Foreign key ke template notifikasi yang digunakan
            $table->foreignId('id_template')->constrained('notifikasi_templates', 'id_notifikasi_template');

            $table->timestamp('tanggal_terjadwal');
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
