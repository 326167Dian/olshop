<?php

namespace App\Http\Controllers;

use App\Models\GajiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryRekapGajiController extends Controller
{
    /**
     * Modul "Rekap Gaji Pegawai" (adaptasi
     * public/yasfigrup/masuk/modul/mod_rekap_gaji). Ringkasan total gaji semua
     * karyawan untuk satu periode (bulan) sekaligus, dibaca dari `gaji_detail`
     * yang sudah dibuat lewat [[InventoryGajiDetailController]]. KHUSUS pemilik,
     * sama seperti modul Gaji & Slip Gaji.
     */
    public function index()
    {
        $this->gate();

        return view('inventory.rekapgaji.index', ['judul' => 'Inventory']);
    }

    public function tampil(Request $request)
    {
        $this->gate();

        $validated = $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2000'],
        ]);

        $periode = sprintf('%04d-%02d', $validated['tahun'], $validated['bulan']);

        $rows = GajiDetail::with('admin')
            ->where('periode_bulan', $periode)
            ->get()
            ->filter(fn ($d) => $d->admin !== null)
            ->sortBy(fn ($d) => $d->admin->nama_lengkap)
            ->values();

        return view('inventory.rekapgaji.tampil', [
            'judul' => 'Inventory',
            'rows' => $rows,
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'grandTotal' => $rows->sum('total'),
        ]);
    }

    private function gate(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isPemilik(), 403, 'Anda tidak berhak mengakses halaman ini. Fitur ini khusus Pemilik.');
    }
}
