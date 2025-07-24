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
        Schema::create('notifikasi_templates', function (Blueprint $table) {
            $table->id('id_notifikasi_template');
            $table->string('nama_template')->unique();
            $table->integer('hari_set')->nullable()->comment('Jumlah hari sebelum/sesudah jatuh tempo');
            $table->string('judul');
            $table->text('isi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi_templates');
    }
};
