<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_role';
    protected $table = 'roles';
    protected $fillable = ['nama_role'];

    public function akuns()
    {
        return $this->hasMany(Akun::class, 'id_role', 'id_role');
    }
}