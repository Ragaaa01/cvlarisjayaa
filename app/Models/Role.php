<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_role';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_role',
    ];

    /**
     * Mendefinisikan relasi has-many ke model Akun.
     * Satu peran bisa dimiliki oleh banyak akun.
     */
    public function akuns()
    {
        return $this->hasMany(Akun::class, 'id_role');
    }
}
