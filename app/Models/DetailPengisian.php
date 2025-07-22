<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPengisian extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'detail_pengisians';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_detail_pengisian';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_pengisian',
        'id_tabung',
        'id_jenis_tabung',
        'harga_pengisian_saat_itu',
    ];

    /**
     * Mendefinisikan relasi belongs-to ke model Pengisian.
     */
    public function pengisian()
    {
        return $this->belongsTo(Pengisian::class, 'id_pengisian');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model Tabung.
     * (Bisa null jika pelanggan membawa tabung sendiri)
     */
    public function tabung()
    {
        return $this->belongsTo(Tabung::class, 'id_tabung');
    }

    /**
     * Mendefinisikan relasi belongs-to ke model JenisTabung.
     * (Untuk menentukan harga jika tabung milik pelanggan)
     */
    public function jenisTabung()
    {
        return $this->belongsTo(JenisTabung::class, 'id_jenis_tabung');
    }
}
