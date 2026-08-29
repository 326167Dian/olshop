<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modul "Pesan Barang" (module=orders, tabel `orders`). Dinamai SupplierOrder
 * (bukan Order) karena nama model 'Order' sudah dipakai untuk pesanan
 * customer/storefront (tabel order_online) -- dua hal yang sama sekali berbeda.
 */
class SupplierOrder extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id_trbmasuk';
    public $timestamps = false;

    protected $fillable = [
        'id_resto',
        'kd_trbmasuk',
        'tgl_trbmasuk',
        'petugas',
        'id_supplier',
        'nm_supplier',
        'tlp_supplier',
        'alamat_trbmasuk',
        'ttl_trbmasuk',
        'dp_bayar',
        'sisa_bayar',
        'ket_trbmasuk',
        'tandatangan',
        'masuk',
    ];

    protected $casts = [
        'tgl_trbmasuk' => 'date',
    ];

    public function detail()
    {
        return $this->hasMany(SupplierOrderDetail::class, 'kd_trbmasuk', 'kd_trbmasuk');
    }
}
