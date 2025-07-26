<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisTabung extends Model
{
    use HasFactory, SoftDeletes; // Tambahkan SoftDeletes jika perlu

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
     * Mendefinisikan relasi has-many ke model Tabung.
     * Satu jenis tabung bisa dimiliki oleh banyak tabung fisik.
     */
    public function tabungs()
    {
        return $this->hasMany(Tabung::class, 'id_jenis_tabung');
    }
}
