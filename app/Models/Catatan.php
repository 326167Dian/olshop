<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catatan extends Model
{
    protected $table = 'catatan';
    protected $primaryKey = 'id_catatan';
    public $timestamps = false;

    protected $fillable = [
        'tgl',
        'shift',
        'petugas',
        'deskripsi',
    ];

    protected $casts = [
        'tgl' => 'date',
        'waktu' => 'datetime',
    ];
}
