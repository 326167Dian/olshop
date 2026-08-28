<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pto extends Model
{
    protected $table = 'pto';
    protected $primaryKey = 'id_pto';

    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'id_pelanggan',
        'nm_pelanggan',
        'jenis_kelamin',
        'umur',
        'alamat_pelanggan',
        'tlp_pelanggan',
        'tanggal_1',
        'catatan_1',
        'obat_1',
        'masalah_1',
        'tindak_1',
        'tanggal_2',
        'catatan_2',
        'obat_2',
        'masalah_2',
        'tindak_2',
        'tempat_ttd',
        'tanggal_ttd',
        'created_by',
    ];

    protected $casts = [
        'tanggal_1' => 'date',
        'tanggal_2' => 'date',
        'tanggal_ttd' => 'date',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
