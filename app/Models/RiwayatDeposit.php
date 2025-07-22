<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatDeposit extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'riwayat_deposits';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_riwayat_deposit';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_deposit',
        'jenis_aktivitas',
        'jumlah',
        'keterangan',
        'waktu_aktivitas',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'waktu_aktivitas' => 'datetime',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Deposit.
     * Setiap riwayat adalah bagian dari satu dompet deposit.
     */
    public function deposit()
    {
        return $this->belongsTo(Deposit::class, 'id_deposit');
    }
}
