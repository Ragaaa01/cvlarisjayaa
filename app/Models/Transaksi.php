<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'transaksis';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_transaksi';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_orang',
        'total_transaksi',
        'status_valid',
        'tanggal_transaksi',
        'waktu_transaksi',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status_valid' => 'boolean',
        'tanggal_transaksi' => 'date',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Orang.
     * Setiap transaksi dimiliki oleh satu orang.
     */
    public function orang()
    {
        return $this->belongsTo(Orang::class, 'id_orang');
    }

    /**
     * Mendefinisikan relasi has-many ke model TransaksiDetail.
     * Satu transaksi bisa memiliki banyak rincian.
     */
    public function transaksiDetails()
    {
        return $this->hasMany(TransaksiDetail::class, 'id_transaksi');
    }
}
