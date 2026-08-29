<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $table = 'batch';
    protected $primaryKey = 'id_batch';
    public $timestamps = false;

    protected $fillable = [
        'tgl_transaksi',
        'no_batch',
        'exp_date',
        'qty',
        'satuan',
        'kd_transaksi',
        'kd_barang',
        'status',
    ];
}
