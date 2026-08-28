<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Homecare extends Model
{
    protected $table = 'homecare';
    protected $primaryKey = 'id_homecare';
    public $timestamps = true;

    protected $fillable = [
        'id_pelanggan',
        'no_homecare',
        'nama_pasien',
        'umur',
        'alamat',
        'telp',
        'created_by',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function detail()
    {
        return $this->hasMany(HomecareDetail::class, 'id_homecare', 'id_homecare')->orderBy('no_urut');
    }
}
