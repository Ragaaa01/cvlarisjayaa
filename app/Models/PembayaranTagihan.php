<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranTagihan extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_pembayaran_tagihan';
    protected $fillable = ['id_tagihan', 'jumlah_dibayar', 'tanggal_bayar', 'metode_pembayaran', 'keterangan'];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'id_tagihan');
    }
}
