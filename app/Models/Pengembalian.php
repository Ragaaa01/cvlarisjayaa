<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'pengembalians';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_pengembalian';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_tabung',
        'id_transaksi_detail',
        'tanggal_pinjam',
        'waktu_pinjam',
        'tanggal_pengembalian',
        'waktu_pengembalian',
        'jumlah_keterlambatan_bulan',
        'total_denda',
        'deposit',
        'denda_kondisi_tabung',
        'id_status_tabung',
        'sisa_deposit',
        'bayar_tagihan',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_pengembalian' => 'date',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Tabung.
     */
    public function tabung()
    {
        return $this->belongsTo(Tabung::class, 'id_tabung');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model TransaksiDetail.
     */
    public function transaksiDetail()
    {
        return $this->belongsTo(TransaksiDetail::class, 'id_transaksi_detail');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model StatusTabung.
     */
    public function statusTabung()
    {
        return $this->belongsTo(StatusTabung::class, 'id_status_tabung');
    }
}
