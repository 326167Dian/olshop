<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trkasir extends Model
{
    protected $table = 'trkasir';
    protected $primaryKey = 'id_trkasir';
    public $timestamps = false;

    protected $fillable = [
        'kd_trkasir',
        'id_user',
        'petugas',
        'shift',
        'tgl_trkasir',
        'id_pelanggan',
        'nm_pelanggan',
        'tlp_pelanggan',
        'alamat_pelanggan',
        'kodetx',
        'ttl_trkasir',
        'dp_bayar',
        'diskon1',
        'diskon2',
        'sisa_bayar',
        'ket_trkasir',
        'id_carabayar',
        'jenistx',
        'tipetx',
        'waktu_trx',
        'poin_awal',
        'tambahan_poin',
        'redeem_poin',
    ];

    protected $casts = [
        'tgl_trkasir' => 'date',
        'waktu_trx' => 'datetime',
    ];

    public function detail()
    {
        return $this->hasMany(TrkasirDetail::class, 'kd_trkasir', 'kd_trkasir');
    }
}
