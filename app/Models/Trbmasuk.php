<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trbmasuk extends Model
{
    protected $table = 'trbmasuk';
    protected $primaryKey = 'id_trbmasuk';
    public $timestamps = false;

    protected $fillable = [
        'id_resto',
        'petugas',
        'kd_trbmasuk',
        'kd_orders',
        'tgl_trbmasuk',
        'id_supplier',
        'nm_supplier',
        'tlp_supplier',
        'alamat_trbmasuk',
        'ttl_trbmasuk',
        'dp_bayar',
        'sisa_bayar',
        'ket_trbmasuk',
        'jatuhtempo',
        'carabayar',
        'jenis',
        'tgl_lunas',
        'petugas_lunas',
    ];

    protected $casts = [
        'tgl_trbmasuk' => 'date',
    ];

    public function detail()
    {
        return $this->hasMany(TrbmasukDetail::class, 'kd_trbmasuk', 'kd_trbmasuk');
    }
}
