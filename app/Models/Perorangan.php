<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perorangan extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_perorangan';
    protected $fillable = ['id_perusahaan', 'nama_lengkap', 'nik', 'no_telepon', 'alamat'];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }

    public function akun()
    {
        return $this->hasOne(Akun::class, 'id_perorangan');
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'id_perorangan');
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'id_perorangan');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'id_perorangan');
    }
}
