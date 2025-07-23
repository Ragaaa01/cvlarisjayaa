<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'tagihans';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_tagihan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_akun',
        'total_tagihan',
        'jumlah_biaya_aktual', // <-- TAMBAHKAN INI
        'jumlah_top_up',
        'jumlah_dibayar',
        'sisa',
        'status_tagihan',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Akun.
     * Setiap tagihan dimiliki oleh satu akun.
     */
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }

    /**
     * Mendefinisikan relasi has-many ke model PembayaranTagihan.
     * Satu tagihan bisa memiliki banyak catatan pembayaran.
     */
    public function pembayaranTagihans()
    {
        return $this->hasMany(PembayaranTagihan::class, 'id_tagihan');
    }
}
