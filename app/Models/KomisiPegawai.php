<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomisiPegawai extends Model
{
    protected $table = 'komisi_pegawai';
    protected $primaryKey = 'id_komisi';
    public $timestamps = false;

    protected $fillable = [
        'kd_trkasir',
        'id_dtrkasir',
        'id_admin',
        'ttl_komisi',
        'tgl_komisi',
        'status_komisi',
    ];

    protected $casts = [
        'tgl_komisi' => 'date',
    ];
}
