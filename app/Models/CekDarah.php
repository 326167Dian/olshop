<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CekDarah extends Model
{
    protected $table = 'cekdarah';
    protected $primaryKey = 'id_cekdarah';
    public $timestamps = false;

    protected $fillable = [
        'id_pelanggan',
        'gula',
        'asamurat',
        'kolesterol',
        'tensi',
        'petugas',
        'waktu',
    ];

    protected $casts = [
        'waktu' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
