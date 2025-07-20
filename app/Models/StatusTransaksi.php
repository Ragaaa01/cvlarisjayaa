<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusTransaksi extends Model
{
    use HasFactory;
     protected $primaryKey = 'id_status_transaksi';
    protected $fillable = ['status_transaksi'];

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'id_status_transaksi');
    }
}
