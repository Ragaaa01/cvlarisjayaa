<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPeminjaman extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'detail_peminjamans';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_detail_peminjaman';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_peminjaman',
        'id_tabung',
        'id_jenis_tabung',
        'harga_pinjam_saat_itu',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Peminjaman.
     * Setiap detail adalah bagian dari satu peminjaman.
     */
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model Tabung.
     * Setiap detail merujuk pada satu tabung spesifik.
     */
    public function tabung()
    {
        return $this->belongsTo(Tabung::class, 'id_tabung');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model JenisTabung.
     * Setiap detail merujuk pada satu jenis tabung.
     */
    public function jenisTabung()
    {
        return $this->belongsTo(JenisTabung::class, 'id_jenis_tabung');
    }
}
