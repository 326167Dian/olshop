<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuStok extends Model
{
    protected $table = 'kartu_stok';
    protected $primaryKey = 'id_kartu';
    public $timestamps = false;

    protected $fillable = [
        'kode_transaksi',
        'tgl_sekarang',
    ];
}
