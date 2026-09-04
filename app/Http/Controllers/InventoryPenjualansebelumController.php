<?php

namespace App\Http\Controllers;

use App\Models\CaraBayar;
use App\Models\Trkasir;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class InventoryPenjualansebelumController extends Controller
{
    /**
     * Modul "Edit/Retur/Hapus Penjualan" (module=penjualansebelumnya di legacy, flag admin
     * 'penjualansebelum'), mengikuti public/apotekberlian/masuk/modul/mod_trkasir/
     * trkasir_tes.php + penjualansebelum_serverside.php.
     *
     * BUKAN modul transaksi baru dan BUKAN fitur retur terpisah -- persis pola byrkredit
     * (lihat InventoryByrkreditController): satu-satunya beda nyata dari modul Penjualan/
     * Kasir utama (module=trkasir, flag 'tpk') adalah RENTANG TANGGAL yang ditampilkan di
     * daftar (kemarin s.d. 360 hari lalu -- pelengkap dari dashboard utama yang hanya
     * menampilkan transaksi HARI INI, lihat InventoryTrkasirController::data()) dan gerbang
     * izinnya (flag 'penjualansebelum', terpisah dari 'tpk', supaya staf yang boleh
     * mengoreksi transaksi lama belum tentu boleh membuka layar kasir harian, atau
     * sebaliknya). Kolom tabelnya juga lebih ringkas dari dashboard utama (tidak ada
     * Shift/Petugas), persis $columns di penjualansebelum_serverside.php.
     *
     * Tautan EDIT/HAPUS/PRINT/KWITANSI/INVOICE di dropdown Aksi legacy
     * (penjualansebelum_serverside.php:91-95) semuanya menunjuk ke endpoint module=trkasir
     * yang SAMA PERSIS dengan yang sudah dibangun di InventoryTrkasirController -- tidak
     * ada logika edit/hapus/cetak baru di sini sama sekali, cukup dialiaskan lewat rute
     * baru yang digerbang 'penjualansebelum' (lihat routes/web.php), termasuk semua
     * endpoint pendukung layar edit (item/bundle/batch/pelanggan picker, detail
     * add/update-qty/destroy) supaya staf yang HANYA punya flag 'penjualansebelum' (tanpa
     * 'tpk') tetap bisa memakai layar edit itu sepenuhnya -- lihat catatan $routePrefix di
     * InventoryTrkasirController::edit(). "Retur" (seperti pada byrkredit) juga bukan fitur
     * terpisah di legacy -- dikerjakan lewat form edit item yang sama (kurangi qty/hapus
     * baris untuk merefleksikan barang yang dikembalikan pelanggan).
     *
     * ETIKET SENGAJA tidak ada di dropdown modul ini (beda dari dropdown utama trkasir) --
     * legacy sendiri tidak menyediakan tautan etiket di penjualansebelum_serverside.php.
     */
    public function index()
    {
        return view('inventory.penjualansebelum.index', ['judul' => 'Inventory']);
    }

    public function data()
    {
        $admin = Auth::guard('admin')->user();
        $isPemilik = $admin && $admin->isPemilik();

        $tglKemarin = now()->subDay()->toDateString();
        $tglAkhir = now()->subDays(360)->toDateString();

        $query = Trkasir::query()
            ->whereBetween('tgl_trkasir', [$tglAkhir, $tglKemarin])
            ->orderByDesc('id_trkasir');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tgl_trkasir', fn ($row) => $row->tgl_trkasir?->format('Y-m-d'))
            ->editColumn('ttl_trkasir', fn ($row) => number_format($row->ttl_trkasir, 0, ',', '.'))
            ->addColumn('nm_carabayar', fn ($row) => optional(CaraBayar::find($row->id_carabayar))->nm_carabayar)
            ->addColumn('aksi', function ($row) use ($isPemilik) {
                $html = '<div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                    <div class="dropdown-menu p-2 shadow" style="min-width:170px;">';

                if ($isPemilik) {
                    $html .= '<a href="' . route('inventory.penjualansebelum.edit', $row->id_trkasir) . '" class="btn btn-warning btn-sm w-100 mb-1">Edit</a>';
                }

                $html .= '<a href="' . route('inventory.penjualansebelum.struk', $row->id_trkasir) . '" target="_blank" class="btn btn-info btn-sm w-100 mb-1">Print</a>
                    <a href="' . route('inventory.penjualansebelum.kwitansi', $row->id_trkasir) . '" target="_blank" class="btn btn-primary btn-sm w-100 mb-1">Kwitansi</a>
                    <a href="' . route('inventory.penjualansebelum.invoice', $row->id_trkasir) . '" target="_blank" class="btn btn-primary btn-sm w-100 mb-1">Invoice</a>';

                if ($isPemilik) {
                    $html .= '<form action="' . route('inventory.penjualansebelum.destroy', $row->id_trkasir) . '" method="POST" id="delete-penjualansebelum-' . $row->id_trkasir . '">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="button" onclick="confirmDelete(\'delete-penjualansebelum-' . $row->id_trkasir . '\', \'transaksi ' . e($row->kd_trkasir) . '\')" class="btn btn-danger btn-sm w-100">Hapus</button>
                    </form>';
                }

                $html .= '</div></div>';

                return $html;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }
}
