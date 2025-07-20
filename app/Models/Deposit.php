<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_deposit';
    protected $fillable = ['id_peminjaman', 'id_perorangan', 'jumlah_deposit', 'status_deposit'];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman');
    }

    public function perorangan()
    {
        return $this->belongsTo(Perorangan::class, 'id_perorangan');
    }
}
