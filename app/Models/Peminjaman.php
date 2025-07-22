<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'peminjamans';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_peminjaman';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_akun',
        'id_tagihan',
        'tanggal_pinjam',
        'tanggal_aktivitas_terakhir',
        'status_pinjam',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status_pinjam' => 'boolean',
        'tanggal_pinjam' => 'datetime',
        'tanggal_aktivitas_terakhir' => 'datetime',
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
     * (Untuk tagihan biaya sewa awal jika ada)
     */
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'id_tagihan');
    }

    /**
     * Mendefinisikan relasi has-many ke model DetailPeminjaman.
     */
    public function detailPeminjamans()
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_peminjaman');
    }

    /**
     * Mendefinisikan relasi has-many ke model Denda.
     */
    public function dendas()
    {
        return $this->hasMany(Denda::class, 'id_peminjaman');
    }

    /**
     * Mendefinisikan relasi has-one ke model Pengembalian.
     */
    public function pengembalian()
    {
        return $this->hasOne(Pengembalian::class, 'id_peminjaman');
    }
}
