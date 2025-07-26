<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Akun extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'akuns';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_akuns';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_role',
        'id_orang',
        'email',
        'password',
        'status_aktif',
    ];

    /**
     * Atribut yang harus disembunyikan saat serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'status_aktif' => 'boolean',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model Orang.
     */
    public function orang()
    {
        return $this->belongsTo(Orang::class, 'id_orang');
    }

    /**
     * Mendefinisikan relasi has-many ke model Notifikasi.
     */
    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class, 'id_akun');
    }

    /**
     * Mendefinisikan relasi has-many ke model FcmToken.
     */
    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class, 'id_akun');
    }
}
