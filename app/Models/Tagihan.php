<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_tagihan';
    protected $fillable = ['id_perorangan', 'total_tagihan', 'jumlah_dibayar', 'sisa', 'status_tagihan'];

    public function perorangan()
    {
        return $this->belongsTo(Perorangan::class, 'id_perorangan');
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'id_tagihan');
    }

    public function pembayaranTagihans()
    {
        return $this->hasMany(PembayaranTagihan::class, 'id_tagihan');
    }

    public function dendas()
    {
        return $this->hasMany(Denda::class, 'id_tagihan');
    }

    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class, 'id_tagihan');
    }
}
