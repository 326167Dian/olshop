<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrderDetailHist extends Model
{
    protected $table = 'ordersdetail_hist';
    protected $primaryKey = 'id_dtrbmasuk';
    public $timestamps = false;

    protected $fillable = [
        'kd_trbmasuk',
        'id_barang',
        'kd_barang',
        'nmbrg_dtrbmasuk',
        'qty_dtrbmasuk',
        'sat_dtrbmasuk',
        'hnasat_dtrbmasuk',
        'diskon',
        'konversi',
        'hrgsat_dtrbmasuk',
        'hrgjual_dtrbmasuk',
        'hrgttl_dtrbmasuk',
        'qtygrosir_dtrbmasuk',
        'satgrosir_dtrbmasuk',
        'no_batch',
        'exp_date',
        'masuk',
    ];
}
