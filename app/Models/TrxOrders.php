<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxOrders extends Model
{
    protected $table = 'trx_orders';
    protected $primaryKey = 'id_trx_ordrers';
    public $timestamps = false;

    protected $fillable = [
        'kd_trbmasuk',
        'kd_orders',
        'keterangan',
    ];
}
