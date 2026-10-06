<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $timestamps = false;

    protected $fillable = [
        'nm_pelanggan',
        'email',
        'jenis_kelamin',
        'tanggal_lahir',
        'tlp_pelanggan',
        'alamat_pelanggan',
        'ket_pelanggan',
        'unit',
        'total_poin',
    ];

    public function cekDarah()
    {
        return $this->hasMany(CekDarah::class, 'id_pelanggan', 'id_pelanggan');
    }
}
