<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'deposits';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_deposit';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_akun',
        'saldo',
        'status_deposit',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Akun.
     * Setiap dompet deposit dimiliki oleh satu akun.
     */
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }

    /**
     * Mendefinisikan relasi has-many ke model RiwayatDeposit.
     * Satu dompet deposit memiliki banyak riwayat mutasi.
     */
    public function riwayatDeposits()
    {
        return $this->hasMany(RiwayatDeposit::class, 'id_deposit');
    }
}
