<?php

namespace App\Services;

use App\Http\Controllers\InventoryJurnalkasController;
use App\Models\JenisJurnal;
use App\Models\JurnalKas;

/**
 * Port dari public/apotekberlian/configurasi/fungsi_jurnal_pembayaran_distributor.php --
 * menyinkronkan entri jurnal kas "Pembayaran Distributor" dengan status/nilai transaksi
 * `trbmasuk` (mod_trbmasuk/mod_trbmasukpbf) TERBARU, dipanggil setiap kali transaksi
 * disimpan (insert/update/hapus), apa pun cara bayarnya saat ini.
 *
 * Satu transaksi (dikunci lewat `kd_trbmasuk`, disimpan di jurnal.kd_referensi) cuma
 * boleh punya SATU entri jurnal -- idempoten:
 * - carabayar bukan LUNAS (atau jumlah <= 0) -> hapus entri jurnal yang ada (kalau ada).
 *   Ini mencegah entri lama nyangkut kalau status di-downgrade dari LUNAS ke KREDIT/
 *   KONSINYASI, atau dipakai paksa dengan carabayar='BATAL' saat transaksi dihapus.
 * - carabayar LUNAS & entri jurnal sudah ada -> update nominalnya kalau berbeda dari
 *   sebelumnya (mis. karena detail item diubah).
 * - carabayar LUNAS & belum ada entri jurnal -> catat entri baru (pertama kali lunas).
 *
 * Saldo kas direkonsiliasi lewat InventoryJurnalkasController::recomputeSaldo() (SUM
 * kredit - SUM debit atas SEMUA baris jurnal), bukan ditambah/dikurangi manual seperti
 * legacy punya ubah_saldo_kas() -- supaya konsisten dengan modul Jurnal Kas yang sudah
 * ada (lihat juga InventoryShiftkerjaController::catatPendapatanKeJurnal()).
 */
class JurnalPembayaranDistributorService
{
    public function sinkron(string $kdTrbmasuk, string $namaSupplier, string $carabayar, float $jumlah, string $petugas): void
    {
        $idJenis = JenisJurnal::where('nm_jurnal', 'Pembayaran Distributor')->value('idjenis') ?? 4;

        $jurnalAda = JurnalKas::where('idjenis', $idJenis)
            ->where('kd_referensi', $kdTrbmasuk)
            ->first();

        if ($carabayar !== 'LUNAS' || $jumlah <= 0) {
            if ($jurnalAda) {
                $jurnalAda->delete();
                app(InventoryJurnalkasController::class)->recomputeSaldo();
            }

            return;
        }

        $ket = 'Pembayaran Distributor ' . $namaSupplier . ' - ' . $kdTrbmasuk;

        if ($jurnalAda) {
            if ((float) $jurnalAda->debit !== $jumlah) {
                $jurnalAda->update([
                    'debit' => $jumlah,
                    'ket' => $ket,
                    'petugas' => $petugas,
                    'current' => now(),
                ]);
                app(InventoryJurnalkasController::class)->recomputeSaldo();
            }

            return;
        }

        JurnalKas::create([
            'tanggal' => now()->toDateString(),
            'ket' => $ket,
            'petugas' => $petugas,
            'idjenis' => $idJenis,
            'kd_referensi' => $kdTrbmasuk,
            'debit' => $jumlah,
            'kredit' => 0,
            'carabayar' => 'TRANSFER',
            'current' => now(),
        ]);

        app(InventoryJurnalkasController::class)->recomputeSaldo();
    }
}
