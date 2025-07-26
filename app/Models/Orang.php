<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orang extends Model
{
    use HasFactory;
    protected $table = 'orangs';
    protected $primaryKey = 'id_orang';
    protected $fillable = ['nama_lengkap', 'nik', 'no_telepon', 'alamat'];

    public function akun()
    {
        return $this->hasOne(Akun::class, 'id_orang');
    }

    public function perusahaan()
    {
        return $this->belongsToMany(Perusahaan::class, 'orang_perusahaans', 'id_orang', 'id_perusahaan')
            ->withPivot('status');
    }
}
