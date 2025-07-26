<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'pembayarans';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_pembayaran';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_orang',
        'total_transaksi',
        'jumlah_pembayaran',
        'metode_pembayaran',
        'nomor_referensi',
        'tanggal_pembayaran',
        'waktu_pembayaran',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_pembayaran' => 'date',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Orang.
     * Setiap pembayaran dilakukan oleh satu orang.
     */
    public function orang()
    {
        return $this->belongsTo(Orang::class, 'id_orang');
    }
}
