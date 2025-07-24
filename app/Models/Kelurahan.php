<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'kelurahans';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_kelurahan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_kecamatan',
        'nama_kelurahan',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Kecamatan.
     * Setiap kelurahan dimiliki oleh satu kecamatan.
     */
    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan');
    }

    /**
     * Mendefinisikan relasi has-many ke model Orang.
     * Satu kelurahan bisa memiliki banyak data orang.
     */
    public function orangs()
    {
        return $this->hasMany(Orang::class, 'id_kelurahan');
    }

    /**
     * Mendefinisikan relasi has-many ke model Mitra.
     * Satu kelurahan bisa memiliki banyak data mitra.
     */
    public function mitras()
    {
        return $this->hasMany(Mitra::class, 'id_kelurahan');
    }
}
