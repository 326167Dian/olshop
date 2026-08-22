<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'satuan';
    protected $primaryKey = 'id_satuan';
    public $timestamps = false;

    protected $fillable = [
        'nm_satuan',
        'deskripsi',
    ];

    /**
     * Relasi: satu Unit punya banyak Produk
     */
}
