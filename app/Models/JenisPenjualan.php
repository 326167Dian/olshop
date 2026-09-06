<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPenjualan extends Model
{
    protected $table = 'jenispenjualan';
    protected $primaryKey = 'id_penjualan';
    public $timestamps = false;

    protected $fillable = [
        'nm_penjualan',
    ];
}
