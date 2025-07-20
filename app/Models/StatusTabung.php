<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusTabung extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_status_tabung';
    protected $fillable = ['status_tabung'];

    public function tabungs()
    {
        return $this->hasMany(Tabung::class, 'id_status_tabung');
    }
}
