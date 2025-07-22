<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'perusahaans';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_perusahaan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_perusahaan',
        'alamat_perusahaan',
    ];

    /**
     * Mendefinisikan relasi many-to-many ke model Orang.
     */
    public function orangs()
    {
        return $this->belongsToMany(Orang::class, 'orang_perusahaans', 'id_perusahaan', 'id_orang')
            ->withPivot('status') // Mengambil kolom 'status' dari tabel pivot
            ->withTimestamps();
    }
}
