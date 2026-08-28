<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPelangganObat extends Model
{
    protected $table = 'riwayat_pelanggan_obat';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_riwayat',
        'kd_barang',
        'nm_barang',
        'aturan_pakai',
    ];

    public function riwayat()
    {
        return $this->belongsTo(RiwayatPelanggan::class, 'id_riwayat');
    }
}
