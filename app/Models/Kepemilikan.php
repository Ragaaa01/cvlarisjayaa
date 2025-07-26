<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kepemilikan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'kepemilikans';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_kepemilikan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'keterangan_kepemilikan',
    ];

    /**
     * Mendefinisikan relasi has-many ke model Tabung.
     * Satu jenis kepemilikan bisa dimiliki oleh banyak tabung.
     */
    public function tabungs()
    {
        return $this->hasMany(Tabung::class, 'id_kepemilikan');
    }
}
