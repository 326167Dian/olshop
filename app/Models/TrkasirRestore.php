<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Snapshot baris trkasir+trkasir_detail yang dihapus lewat HAPUS (destroy()), satu baris
 * per baris trkasir_detail yang ikut terhapus -- mengikuti INSERT INTO trkasir_restore di
 * public/apotekberlian/masuk/modul/mod_trkasir/aksi_trkasir.php (act=hapus). Tabel ini
 * sudah ada di DB produksi (dibuat/di-ALTER otomatis oleh pastikan_skema_perubahan_trkasir()
 * legacy) -- tidak perlu migration baru, skema sudah dikonfirmasi cocok persis.
 */
class TrkasirRestore extends Model
{
    protected $table = 'trkasir_restore';
    protected $primaryKey = 'id_butrkasir';
    public $timestamps = false;

    protected $fillable = [
        'kd_trkasir',
        'petugas',
        'shift',
        'tgl_trkasir',
        'nm_pelanggan',
        'tlp_pelanggan',
        'alamat_pelanggan',
        'ttl_trkasir',
        'dp_bayar',
        'diskon1',
        'diskon2',
        'sisa_bayar',
        'ket_trkasir',
        'id_carabayar',
        'id_dtrkasir',
        'id_barang',
        'kd_barang',
        'nmbrg_dtrkasir',
        'qty_dtrkasir',
        'sat_dtrkasir',
        'hrgjual_dtrkasir',
        'hrgttl_dtrkasir',
        'disc',
        'resep',
        'modal',
        'profit',
        'no_batch',
        'exp_date',
        'waktu',
        'tipe',
        'komisi',
        'idadmin',
        'kd_bundle',
        'nm_bundle',
        'tipetx',
        'waktu_hapus',
        'id_admin_hapus',
        'id_user',
        'id_pelanggan',
        'kodetx',
        'jenistx',
        'waktu_trx',
        'poin_awal',
        'tambahan_poin',
        'redeem_poin',
    ];
}
