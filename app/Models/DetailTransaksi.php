<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_detail_transaksi';
    protected $fillable = ['id_transaksi', 'id_tabung', 'id_jenis_transaksi', 'harga'];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }

    public function tabung()
    {
        return $this->belongsTo(Tabung::class, 'id_tabung');
    }

    public function jenisTransaksi()
    {
        return $this->belongsTo(JenisTransaksi::class, 'id_jenis_transaksi');
    }

    public function peminjaman()
    {
        return $this->hasOne(Peminjaman::class, 'id_detail_transaksi');
    }
}
