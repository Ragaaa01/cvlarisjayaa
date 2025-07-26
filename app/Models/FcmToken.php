<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NotificationChannels\Fcm\FcmChannel;

class FcmToken extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'fcm_tokens';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_fcm_token';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_akun',
        'token',
        'nama_perangkat',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Akun.
     * Setiap token dimiliki oleh satu akun.
     */
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }
}
