<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrangMitra extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'orang_mitras';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_orang_mitra';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_orang',
        'id_mitra',
        'status_valid',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status_valid' => 'boolean',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Orang.
     */
    public function orang()
    {
        return $this->belongsTo(Orang::class, 'id_orang');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model Mitra.
     */
    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'id_mitra');
    }
}
