<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomecareDetail extends Model
{
    protected $table = 'homecare_detail';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_homecare',
        'no_urut',
        'tgl_kunjungan',
        'catatan_apoteker',
    ];
}
