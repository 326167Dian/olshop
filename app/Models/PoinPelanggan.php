<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoinPelanggan extends Model
{
    protected $table = 'poin_pelanggan';
    protected $primaryKey = 'id_poin';
    public $timestamps = false;

    protected $fillable = [
        'nm_outlet',
        'is_outlet',
        'min_penjualan',
        'is_kelipatan',
        'poin_pelanggan',
        'is_active',
    ];
}
