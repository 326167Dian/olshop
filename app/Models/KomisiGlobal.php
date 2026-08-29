<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomisiGlobal extends Model
{
    protected $table = 'komisiglobal';
    protected $primaryKey = 'id_komisiglobal';
    public $timestamps = false;

    protected $fillable = [
        'nilai',
        'tgl',
        'petugas',
        'status',
    ];

    protected $casts = [
        'tgl' => 'date',
    ];
}
