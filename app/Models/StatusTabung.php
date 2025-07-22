<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusTabung extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'status_tabungs';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_status_tabung';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'status_tabung',
    ];

    /**
     * Mendefinisikan relasi has-many ke model Tabung.
     * Satu status bisa dimiliki oleh banyak tabung.
     */
    public function tabungs()
    {
        return $this->hasMany(Tabung::class, 'id_status_tabung');
    }
}
