<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tabung extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'tabungs';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_tabung';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_tabung',
        'id_jenis_tabung',
        'id_status_tabung',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model JenisTabung.
     */
    public function jenisTabung()
    {
        return $this->belongsTo(JenisTabung::class, 'id_jenis_tabung');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model StatusTabung.
     */
    public function statusTabung()
    {
        return $this->belongsTo(StatusTabung::class, 'id_status_tabung');
    }

    /**
     * Mendefinisikan relasi has-many ke model DetailPeminjaman.
     * Satu tabung bisa memiliki banyak riwayat peminjaman.
     */
    public function detailPeminjamans()
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_tabung');
    }
}
