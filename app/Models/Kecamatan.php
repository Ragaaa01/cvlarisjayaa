<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'kecamatans';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_kecamatan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_kabupaten',
        'nama_kecamatan',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Kabupaten.
     * Setiap kecamatan dimiliki oleh satu kabupaten.
     */
    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'id_kabupaten');
    }

    /**
     * Mendefinisikan relasi has-many ke model Kelurahan.
     * Satu kecamatan memiliki banyak kelurahan.
     */
    public function kelurahans()
    {
        return $this->hasMany(Kelurahan::class, 'id_kecamatan');
    }
}
