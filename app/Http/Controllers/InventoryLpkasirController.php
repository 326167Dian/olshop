<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\CaraBayar;
use App\Models\Trkasir;
use App\Models\TrkasirDetail;
use Illuminate\Http\Request;

class InventoryLpkasirController extends Controller
{
    /**
     * Modul "Penjualan" (grup Laporan, flag admin `lpkasir`), mengikuti
     * public/apotekberlian/masuk/modul/mod_lappenjualan/lappenjualan.php (form filter +
     * act=view) dan lappenjualan_excel.php (export Excel). BEDA dari modul transaksi
     * Penjualan/Kasir (`tpk`) -- ini murni laporan baca-saja atas trkasir/trkasir_detail
     * pada rentang tanggal + shift + petugas tertentu, digerbang flag admin terpisah.
     *
     * `act=view` legacy TIDAK memakai FPDF sama sekali (beda dari lpitem/lpbrgmasuk) --
     * hanya halaman HTML biasa yang dibuka di tab baru (form target="_blank"), jadi di
     * sini diporting sebagai halaman Blade biasa (dibuka via GET + target=_blank, bukan
     * POST -- ini laporan baca-saja, mengikuti konvensi GET-untuk-baca yang sudah
     * dipakai di seluruh port ini), bukan PDF. `mod_laporan/cetak_penjualan.php`
     * (satu-satunya file FPDF yang namanya mirip) dikonfirmasi dead code -- tidak ada
     * link ke sana di mana pun -- tidak diporting.
     *
     * Filter shift mengikuti legacy persis: 1=Pagi, 2=Sore, 3=Malam, 4=Semua Shift
     * (khusus untuk laporan ini -- bukan berarti tabel `namashift` benar-benar punya 3
     * shift, modul Buka/Tutup Kasir sendiri cuma mengenal Pagi/Sore; filter di sini
     * murni nilai kolom `trkasir.shift`, independen dari `namashift`).
     *
     * Filter Petugas ditambahkan 2026-09-05 -- salinan lokal berkas legacy ini ternyata
     * sudah usang (dikonfirmasi pengguna langsung dari tangkapan layar aplikasi produksi
     * yang sudah punya dropdown "PETUGAS", default "ALL", di antara Tanggal Akhir dan
     * SHIFT). Karena berkas sumber yang mutakhir tidak tersedia untuk dibaca, field ini
     * disimpulkan mengacu ke `trkasir.id_user` (Petugas Pelayanan) -- konsep "petugas"
     * yang sudah dipakai konsisten di seluruh modul Penjualan/Kasir (atribusi komisi,
     * dsb), dan levelnya (per transaksi, bukan per baris) cocok dengan struktur laporan
     * ini yang memang menampilkan satu blok per transaksi. Filter diterapkan di SEMUA
     * agregat (tampil/excel/breakdown cara bayar) supaya grand total selalu konsisten
     * dengan data yang ditampilkan, sama seperti filter shift.
     */
    public function index()
    {
        return view('inventory.lpkasir.index', [
            'judul' => 'Inventory',
            'petugasList' => Admin::orderBy('nama_lengkap')->get(['id_admin', 'nama_lengkap']),
        ]);
    }

    /**
     * Rincian per transaksi + ringkasan per cara bayar + grand total, mengikuti
     * act=view.
     */
    public function tampil(Request $request)
    {
        $validated = $this->validateFilter($request);
        $shiftArr = $this->resolveShiftArray((int) $validated['shift']);
        $idUser = $validated['id_user'] ?? null;

        $transaksi = Trkasir::whereIn('shift', $shiftArr)
            ->whereBetween('tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->when($idUser, fn ($q) => $q->where('id_user', $idUser))
            ->with(['detail', 'caraBayar'])
            ->orderBy('id_trkasir')
            ->get()
            ->map(function ($trx) {
                $subtotal = $trx->detail->sum('hrgttl_dtrkasir');

                $trx->subtotal = $subtotal;
                $trx->diskon = $subtotal - $trx->ttl_trkasir;

                return $trx;
            });

        [$breakdown, $grandTotal] = $this->hitungBreakdownCarabayar($shiftArr, $validated['tgl_awal'], $validated['tgl_akhir'], $idUser);

        return view('inventory.lpkasir.tampil', [
            'judul' => 'Inventory',
            'transaksi' => $transaksi,
            'breakdown' => $breakdown,
            'grandTotal' => $grandTotal,
            'shiftLabel' => $validated['shift'] < 4 ? $validated['shift'] : implode(',', $shiftArr),
            'petugasNama' => $idUser ? optional(Admin::find($idUser))->nama_lengkap : 'Semua Petugas',
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
        ]);
    }

    /**
     * Export Excel -- BEDA bentuk dari act=view: bukan per-transaksi, tapi diagregasi
     * per kd_barang di seluruh rentang tanggal (mengikuti GROUP BY kd_barang legacy),
     * plus ringkasan per cara bayar + grand total yang sama.
     */
    public function excel(Request $request)
    {
        $validated = $this->validateFilter($request);
        $shiftArr = $this->resolveShiftArray((int) $validated['shift']);
        $idUser = $validated['id_user'] ?? null;

        $rows = TrkasirDetail::query()
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->whereIn('trkasir.shift', $shiftArr)
            ->whereBetween('trkasir.tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->when($idUser, fn ($q) => $q->where('trkasir.id_user', $idUser))
            ->groupBy('trkasir_detail.kd_barang', 'trkasir_detail.nmbrg_dtrkasir', 'trkasir_detail.sat_dtrkasir')
            ->orderBy('trkasir_detail.nmbrg_dtrkasir')
            ->selectRaw('trkasir_detail.kd_barang, trkasir_detail.nmbrg_dtrkasir, trkasir_detail.sat_dtrkasir,
                SUM(trkasir_detail.qty_dtrkasir) as qty_total, SUM(trkasir_detail.hrgttl_dtrkasir) as total_harga')
            ->get();

        [$breakdown, $grandTotal] = $this->hitungBreakdownCarabayar($shiftArr, $validated['tgl_awal'], $validated['tgl_akhir'], $idUser);

        return response()->view('inventory.lpkasir.excel', [
            'rows' => $rows,
            'breakdown' => $breakdown,
            'grandTotal' => $grandTotal,
            'shiftLabel' => $validated['shift'] < 4 ? $validated['shift'] : implode(',', $shiftArr),
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="Laporan_data_penjualan.xls"',
        ]);
    }

    private function validateFilter(Request $request): array
    {
        return $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'shift' => 'required|integer|min:1|max:4',
            'id_user' => 'nullable|integer|exists:admin,id_admin',
        ]);
    }

    /** @return int[] */
    private function resolveShiftArray(int $shift): array
    {
        return $shift < 4 ? [$shift] : [1, 2, 3];
    }

    /** @return array{0: \Illuminate\Support\Collection, 1: float} */
    private function hitungBreakdownCarabayar(array $shiftArr, string $tglAwal, string $tglAkhir, ?int $idUser = null): array
    {
        $breakdown = CaraBayar::orderBy('urutan')->get()->map(function ($cb) use ($shiftArr, $tglAwal, $tglAkhir, $idUser) {
            $total = (float) Trkasir::whereIn('shift', $shiftArr)
                ->whereBetween('tgl_trkasir', [$tglAwal, $tglAkhir])
                ->where('id_carabayar', $cb->id_carabayar)
                ->when($idUser, fn ($q) => $q->where('id_user', $idUser))
                ->sum('ttl_trkasir');

            return ['nm_carabayar' => $cb->nm_carabayar, 'total' => $total];
        });

        return [$breakdown, $breakdown->sum('total')];
    }
}
