<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinkronisasiStokLog extends Model
{
    protected $table = 'sinkronisasi_stok_logs';
    public $timestamps = false;

    protected $fillable = [
        'kode_sinkron',
        'id_barang',
        'kd_barang',
        'nm_barang',
        'stok_sebelum',
        'stok_sesudah',
        'selisih',
        'id_admin',
        'waktu',
    ];

    protected $casts = [
        'waktu' => 'datetime',
    ];
}
