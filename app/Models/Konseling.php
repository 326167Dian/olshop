<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konseling extends Model
{
    protected $table = 'konseling';
    protected $primaryKey = 'id_konseling';
    public $timestamps = false;

    protected $fillable = [
        'id_pelanggan',
        'nm_pelanggan',
        'tgl_konseling',
        'id_admin',
        'nama_lengkap',
        'nama_dokter',
        'diagnosa',
        'riwayat_penyakit',
        'riwayat_alergi',
        'keluhan',
        'visite',
        'tindakan',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
