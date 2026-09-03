<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaktuKerja extends Model
{
    protected $table = 'waktukerja';
    protected $primaryKey = 'id_shift';
    public $timestamps = false;

    protected $fillable = [
        'petugasbuka',
        'petugastutup',
        'shift',
        'tanggal',
        'waktubuka',
        'waktututup',
        'saldoawal',
        'saldoakhir',
        'status',
    ];

    public function namaShift()
    {
        return $this->belongsTo(NamaShift::class, 'shift', 'shift');
    }
}
