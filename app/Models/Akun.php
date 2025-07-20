<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Akun extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_akun';
    protected $table = 'akuns';
    protected $fillable = ['id_role', 'id_perorangan', 'email', 'password', 'status_aktif', 'remember_token'];
    protected $hidden = ['password', 'remember_token'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function perorangan()
    {
        return $this->belongsTo(Perorangan::class, 'id_perorangan', 'id_perorangan');
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'id_akun', 'id_akun');
    }

    public function getAuthPassword()
    {
        return $this->password;
    }
}