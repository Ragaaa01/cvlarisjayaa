<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisTransaksiDetail extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'jenis_transaksi_details';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_jenis_transaksi_detail';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'jenis_transaksi',
    ];

    /**
     * Mendefinisikan relasi has-many ke model TransaksiDetail.
     * Satu jenis transaksi bisa ada di banyak detail transaksi.
     */
    public function transaksiDetails()
    {
        return $this->hasMany(TransaksiDetail::class, 'id_jenis_transaksi_detail');
    }
}
