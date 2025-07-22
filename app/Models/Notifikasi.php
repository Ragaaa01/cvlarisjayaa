<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'notifikasis';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_notifikasi';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_akun',
        'id_tagihan',
        'id_template',
        'tanggal_terjadwal',
        'status_baca',
        'waktu_dikirim',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_terjadwal' => 'datetime',
        'waktu_dikirim' => 'datetime',
        'status_baca' => 'boolean',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Akun.
     */
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model Tagihan.
     */
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'id_tagihan');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model NotifikasiTemplate.
     */
    public function template()
    {
        return $this->belongsTo(NotifikasiTemplate::class, 'id_template');
    }
}
