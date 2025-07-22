<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPengembalian extends Model
{
    use HasFactory;
    protected $table = 'detail_pengembalians';
    protected $primaryKey = 'id_detail_pengembalian';
    protected $fillable = ['id_pengembalian', 'id_tabung', 'kondisi_tabung'];

    public function pengembalian()
    {
        return $this->belongsTo(Pengembalian::class, 'id_pengembalian');
    }

    public function tabung()
    {
        return $this->belongsTo(Tabung::class, 'id_tabung');
    }
}
