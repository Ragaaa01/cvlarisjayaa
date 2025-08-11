<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = false;

    protected $fillable = [
        'id_orang',
        'total_transaksi',
        'status_valid',
        'tanggal_transaksi',
        'waktu_transaksi',
    ];

    public function orang()
    {
        return $this->belongsTo(Orang::class, 'id_orang', 'id_orang');
    }

    public function transaksiDetails()
    {
        return $this->hasMany(TransaksiDetail::class, 'id_transaksi', 'id_transaksi');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_orang', 'id_orang')
                   ->whereColumn('pembayarans.tanggal_pembayaran', 'transaksis.tanggal_transaksi');
    }
}