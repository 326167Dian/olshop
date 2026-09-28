<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    protected $table = 'gaji';
    protected $primaryKey = 'id_gaji';
    public $timestamps = false;

    protected $fillable = [
        'id_admin',
        'gaji_harian',
        'transportasi_harian',
        'rate_lembur',
        'potongan_telat_15_30',
        'potongan_telat_30_60',
        'potongan_telat_60_lebih',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function details()
    {
        return $this->hasMany(GajiDetail::class, 'id_gaji', 'id_gaji');
    }
}
