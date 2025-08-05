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
        Schema::table('notifikasis', function (Blueprint $table) {
            // Tambahkan kolom 'judul' setelah 'id_template'
            $table->string('judul')->after('id_template')->nullable();
            // Tambahkan kolom 'isi' setelah 'judul'
            $table->text('isi')->after('judul')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifikasis', function (Blueprint $table) {
            $table->dropColumn(['judul', 'isi']);
        });
    }
};
