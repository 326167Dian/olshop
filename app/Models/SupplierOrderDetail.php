<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrderDetail extends Model
{
    protected $table = 'ordersdetail';
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

    public function order()
    {
        return $this->belongsTo(SupplierOrder::class, 'kd_trbmasuk', 'kd_trbmasuk');
    }

    public function barang()
    {
        return $this->belongsTo(Product::class, 'id_barang', 'id_barang');
    }
}
