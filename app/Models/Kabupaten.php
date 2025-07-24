<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'kabupatens';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_kabupaten';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_provinsi',
        'nama_kabupaten',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Provinsi.
     * Setiap kabupaten dimiliki oleh satu provinsi.
     */
    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'id_provinsi');
    }

    /**
     * Mendefinisikan relasi has-many ke model Kecamatan.
     * Satu kabupaten memiliki banyak kecamatan.
     */
    public function kecamatans()
    {
        return $this->hasMany(Kecamatan::class, 'id_kabupaten');
    }
}
