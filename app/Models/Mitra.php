<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'mitras';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_mitra';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_mitra',
        'id_kelurahan',
        'alamat_mitra',
        'verified',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified' => 'boolean',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Kelurahan.
     * Setiap mitra berlokasi di satu kelurahan.
     */
    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'id_kelurahan');
    }

    /**
     * Mendefinisikan relasi many-to-many ke model Orang.
     */
    public function orangs()
    {
        return $this->belongsToMany(Orang::class, 'orang_mitras', 'id_mitra', 'id_orang')
            ->withPivot('status_valid')
            ->withTimestamps();
    }
}
