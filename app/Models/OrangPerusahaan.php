<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrangPerusahaan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'orang_perusahaans';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_orang_perusahaan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_orang',
        'id_perusahaan',
        'status',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Orang.
     */
    public function orang()
    {
        return $this->belongsTo(Orang::class, 'id_orang');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model Perusahaan.
     */
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }
}
