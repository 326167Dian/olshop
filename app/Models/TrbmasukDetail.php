<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrbmasukDetail extends Model
{
    protected $table = 'trbmasuk_detail';
    protected $primaryKey = 'id_dtrbmasuk';
    public $timestamps = false;

    protected $fillable = [
        'kd_trbmasuk',
        'kd_orders',
        'id_barang',
        'kd_barang',
        'nmbrg_dtrbmasuk',
        'qty_dtrbmasuk',
        'sat_dtrbmasuk',
        'qty_grosir',
        'satgrosir_dtrbmasuk',
        'hnasat_dtrbmasuk',
        'diskon',
        'konversi',
        'hrgsat_dtrbmasuk',
        'hrgjual_dtrbmasuk',
        'hrgttl_dtrbmasuk',
        'no_batch',
        'exp_date',
        'tipe',
        'tipe_barang',
    ];

    protected $casts = [
        'exp_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Trbmasuk::class, 'kd_trbmasuk', 'kd_trbmasuk');
    }

    public function barang()
    {
        return $this->belongsTo(Product::class, 'id_barang', 'id_barang');
    }
}
