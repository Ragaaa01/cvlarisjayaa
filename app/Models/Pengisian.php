<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengisian extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'pengisians';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_pengisian';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_akun',
        'id_tagihan',
        'total_biaya',
        'waktu_transaksi',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'waktu_transaksi' => 'datetime',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Akun.
     */
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model Tagihan.
     */
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'id_tagihan');
    }

    /**
     * Mendefinisikan relasi has-many ke model DetailPengisian.
     */
    public function detailPengisians()
    {
        return $this->hasMany(DetailPengisian::class, 'id_pengisian');
    }
}
