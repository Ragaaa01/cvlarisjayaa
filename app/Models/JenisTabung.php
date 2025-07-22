<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisTabung extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'jenis_tabungs';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_jenis_tabung';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_jenis',
        'harga_pinjam',
        'harga_isi_ulang',
        'nilai_deposit',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'harga_pinjam' => 'decimal:2',
        'harga_isi_ulang' => 'decimal:2',
        'nilai_deposit' => 'decimal:2',
    ];

    /**
     * Mendefinisikan relasi has-many ke model Tabung.
     */
    public function tabungs()
    {
        return $this->hasMany(Tabung::class, 'id_jenis_tabung');
    }
}
