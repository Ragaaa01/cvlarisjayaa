<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifikasiTemplate extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'notifikasi_templates';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_notifikasi_template';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_template',
        'hari_set',
        'judul',
        'isi',
    ];

    /**
     * Mendefinisikan relasi has-many ke model Notifikasi.
     * Satu template bisa digunakan oleh banyak notifikasi.
     */
    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class, 'id_template');
    }
}
