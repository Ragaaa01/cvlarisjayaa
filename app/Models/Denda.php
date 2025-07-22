<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'dendas';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_denda';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_peminjaman',
        'id_akun',
        'jenis_denda',
        'jumlah_denda',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Peminjaman.
     * Setiap denda disebabkan oleh satu peminjaman.
     */
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model Akun.
     * Setiap denda dibebankan kepada satu akun.
     */
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }
}
