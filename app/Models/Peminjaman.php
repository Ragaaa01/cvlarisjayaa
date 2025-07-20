<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_peminjaman';
    protected $fillable = ['id_detail_transaksi', 'tanggal_pinjam', 'tanggal_aktivitas_terakhir', 'status_pinjam'];

    public function detailTransaksi()
    {
        return $this->belongsTo(DetailTransaksi::class, 'id_detail_transaksi');
    }

    public function deposit()
    {
        return $this->hasOne(Deposit::class, 'id_peminjaman');
    }

    public function dendas()
    {
        return $this->hasMany(Denda::class, 'id_peminjaman');
    }

    public function pengembalian()
    {
        return $this->hasOne(Pengembalian::class, 'id_peminjaman');
    }
}
