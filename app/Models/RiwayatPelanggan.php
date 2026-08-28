<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPelanggan extends Model
{
    protected $table = 'riwayat_pelanggan';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_pelanggan',
        'id_admin',
        'tgl',
        'diagnosa',
        'foto',
        'foto2',
        'tindakan',
        'followup',
        'tgl_followup',
        'followup_by',
    ];

    protected $casts = [
        'tgl' => 'date',
        'tgl_followup' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function obat()
    {
        return $this->hasMany(RiwayatPelangganObat::class, 'id_riwayat')->orderBy('id');
    }
}
