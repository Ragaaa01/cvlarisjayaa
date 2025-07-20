<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_transaksi';
    protected $fillable = ['id_akun', 'id_perorangan', 'id_perusahaan', 'id_tagihan', 'total_transaksi', 'id_status_transaksi', 'tanggal_transaksi', 'waktu_transaksi'];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }

    public function perorangan()
    {
        return $this->belongsTo(Perorangan::class, 'id_perorangan');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'id_tagihan');
    }

    public function statusTransaksi()
    {
        return $this->belongsTo(StatusTransaksi::class, 'id_status_transaksi');
    }

    public function detailTransaksis()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }
}
