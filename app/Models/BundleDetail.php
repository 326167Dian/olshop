<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleDetail extends Model
{
    protected $table = 'bundle_detail';
    protected $primaryKey = 'idbundle_detail';
    public $timestamps = false;

    protected $fillable = [
        'kd_bundle',
        'id_barang',
        'kd_barang',
        'nm_barang',
        'qty_barang',
        'sat_barang',
        'hrgjual_barang',
        'subtotal',
        'created_at',
        'update_at',
    ];
}
