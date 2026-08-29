<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrbmasukDetailHist extends Model
{
    protected $table = 'trbmasuk_detail_hist';
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
    ];
}
