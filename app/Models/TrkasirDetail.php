<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrkasirDetail extends Model
{
    protected $table = 'trkasir_detail';
    protected $primaryKey = 'id_dtrkasir';
    public $timestamps = false;

    protected $fillable = [
        'kd_trkasir',
        'id_barang',
        'kd_barang',
        'nmbrg_dtrkasir',
        'qty_dtrkasir',
        'sat_dtrkasir',
        'hrgjual_dtrkasir',
        'disc',
        'modal',
        'profit',
        'hrgttl_dtrkasir',
        'no_batch',
        'exp_date',
        'tipe',
        'komisi',
        'idadmin',
        'tipetx',
        'resep',
        'kd_bundle',
        'nm_bundle',
    ];

    protected $casts = [
        'exp_date' => 'date',
        'waktu' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Product::class, 'id_barang', 'id_barang');
    }
}
