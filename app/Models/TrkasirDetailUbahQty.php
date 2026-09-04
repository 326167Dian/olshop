<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrkasirDetailUbahQty extends Model
{
    protected $table = 'trkasir_detail_ubah_qty';
    protected $primaryKey = 'id_log';
    public $timestamps = false;

    protected $fillable = [
        'kd_trkasir',
        'id_dtrkasir',
        'kd_barang',
        'nmbrg_dtrkasir',
        'qty_sebelum',
        'qty_sesudah',
        'hrgttl_sebelum',
        'hrgttl_sesudah',
        'tipetx',
        'id_admin',
    ];
}
