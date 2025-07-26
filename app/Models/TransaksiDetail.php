<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'transaksi_details';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_transaksi_detail';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_transaksi',
        'id_tabung',
        'id_jenis_transaksi_detail',
        'harga',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Transaksi.
     * Setiap detail adalah bagian dari satu transaksi.
     */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model Tabung.
     * (Bisa null jika transaksi tidak melibatkan tabung fisik spesifik)
     */
    public function tabung()
    {
        return $this->belongsTo(Tabung::class, 'id_tabung');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model JenisTransaksiDetail.
     */
    public function jenisTransaksiDetail()
    {
        return $this->belongsTo(JenisTransaksiDetail::class, 'id_jenis_transaksi_detail');
    }
}
