<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'provinsis';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_provinsi';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_provinsi',
    ];

    /**
     * Mendefinisikan relasi has-many ke model Kabupaten.
     * Satu provinsi memiliki banyak kabupaten.
     */
    public function kabupatens()
    {
        return $this->hasMany(Kabupaten::class, 'id_provinsi');
    }
}
