<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisTabung extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_jenis_tabung';
    protected $fillable = ['nama_jenis', 'harga_sewa', 'harga_isi_gas', 'nilai_deposit'];

    public function tabungs()
    {
        return $this->hasMany(Tabung::class, 'id_jenis_tabung');
    }
}
