<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orang extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'orangs';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_orang';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_lengkap',
        'nik',
        'no_telepon',
        'id_kelurahan',
        'alamat',
    ];

    /**
     * Mendefinisikan relasi has-one ke model Akun.
     * Satu orang hanya memiliki satu akun.
     */
    public function akun()
    {
        return $this->hasOne(Akun::class, 'id_orang');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model Kelurahan.
     * Setiap orang berlokasi di satu kelurahan.
     */
    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'id_kelurahan');
    }

    /**
     * Mendefinisikan relasi many-to-many ke model Mitra.
     */
    public function mitras()
    {
        return $this->belongsToMany(Mitra::class, 'orang_mitras', 'id_orang', 'id_mitra')
            ->withPivot('status_valid')
            ->withTimestamps();
    }

    /**
     * Mendefinisikan relasi has-many ke model Transaksi.
     * Satu orang bisa memiliki banyak transaksi.
     */
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'id_orang');
    }

    /**
     * Mendefinisikan relasi has-many ke model Pembayaran.
     * Satu orang bisa memiliki banyak riwayat pembayaran.
     */
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'id_orang');
    }
}
