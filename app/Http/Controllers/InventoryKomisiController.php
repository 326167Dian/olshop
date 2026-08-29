<?php

namespace App\Http\Controllers;

use App\Models\KomisiGlobal;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryKomisiController extends Controller
{
    /**
     * Modul "Komisi Pegawai" (module=komisi), mengikuti
     * public/apotekberlian/masuk/modul/mod_komisi/komisi.php + aksi_komisi.php.
     * Tidak diadaptasi: act=input_komisi (form massal by-nama/all lama, sudah tidak
     * ada link aktif ke situ di UI legacy -- digantikan oleh grid massal AJAX di
     * act=tambah) dan act=tutupkomisi/close (tombolnya sudah dikomentari di legacy,
     * tidak reachable dari UI manapun, dan bergantung pada tabel komisi_pegawai yang
     * ditulis oleh alur transaksi kasir, di luar cakupan modul ini).
     * Bug diperbaiki: perhitungan History bulanan di legacy hardcode tahun '2025'
     * (variabel $tahun dihitung tapi tidak dipakai) -- di sini pakai tahun yang
     * benar-benar dipilih/berjalan.
     */
    private array $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index()
    {
        $komisi = Product::where('komisi', '!=', 0)->orderBy('nm_barang')->get();

        return view('inventory.komisi.index', [
            'judul' => 'Inventory',
            'komisi' => $komisi,
            'isPemilik' => Auth::guard('admin')->user()->isPemilik(),
        ]);
    }

    /**
     * "Atur Komisi" -- grid massal, mengikuti case 'tambah'. Hanya pemilik.
     */
    public function massal()
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403, 'Hanya pemilik yang dapat mengatur komisi.');

        $barang = Product::where('stok_barang', '>', 0)
            ->where('hrgsat_barang', '>', 0)
            ->whereRaw('(hrgjual_barang - hrgsat_barang) / hrgsat_barang > 0.5')
            ->orderBy('nm_barang')
            ->get();

        return view('inventory.komisi.massal', [
            'judul' => 'Inventory',
            'barang' => $barang,
        ]);
    }

    /**
     * Simpan komisi per item dari grid massal (AJAX), mengikuti
     * simpandetail_komisi_massal.php.
     */
    public function massalUpdate(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $validated = $request->validate([
            'id_barang' => 'required|integer|exists:barang,id_barang',
            'komisi' => 'nullable|numeric|min:0',
        ]);

        $komisi = $validated['komisi'] ?? 0;

        Product::where('id_barang', $validated['id_barang'])->update(['komisi' => $komisi]);

        return response()->json(['status' => 'ok', 'komisi' => $komisi]);
    }

    /**
     * Edit komisi satu item, mengikuti case 'editkomisi'. Hanya pemilik.
     */
    public function edit(Product $barang)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403, 'Hanya pemilik yang dapat mengubah komisi.');

        return view('inventory.komisi.edit', [
            'judul' => 'Inventory',
            'barang' => $barang,
        ]);
    }

    /**
     * Update komisi satu item, mengikuti act=update_komisi.
     */
    public function update(Request $request, Product $barang)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $validated = $request->validate([
            'metode' => 'required|in:nominal,persentase',
            'komisi' => 'required|numeric|min:0',
        ]);

        if ($validated['metode'] === 'nominal') {
            $barang->update(['komisi' => $validated['komisi']]);
        } else {
            $barang->update(['komisi' => round(($barang->hrgsat_barang * $validated['komisi']) / 100)]);
        }

        return redirect()->route('inventory.komisi.index')->with('success', 'Komisi berhasil diperbarui.');
    }

    /**
     * Hapus komisi satu item (set 0), mengikuti act=hapus&id={id}.
     */
    public function destroy(Product $barang)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $barang->update(['komisi' => 0]);

        return redirect()->route('inventory.komisi.index')->with('success', 'Komisi berhasil dihapus.');
    }

    /**
     * Hapus semua komisi, mengikuti act=hapus&id=all.
     */
    public function destroyAll()
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        Product::where('komisi', '!=', 0)->update(['komisi' => 0]);

        return redirect()->route('inventory.komisi.index')->with('success', 'Semua komisi berhasil dihapus.');
    }

    /**
     * Komisi global (persentase dari harga beli untuk semua petugas), mengikuti
     * case 'global'. Hanya pemilik.
     */
    public function global()
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403, 'Hanya pemilik yang dapat mengatur komisi global.');

        $aktif = KomisiGlobal::where('status', 'ON')->first();
        $persen = $aktif->nilai ?? 0;

        $awalBulan = now()->startOfMonth()->toDateString();
        $hariIni = now()->toDateString();

        $petugasList = DB::table('admin')
            ->where('akses_level', 'petugas')
            ->where('blokir', 'N')
            ->get(['id_admin', 'nama_lengkap']);

        $rows = [];
        $totalKomisi = 0;
        foreach ($petugasList as $p) {
            $totalPenjualan = (float) DB::table('trkasir')
                ->whereBetween('tgl_trkasir', [$awalBulan, $hariIni])
                ->where('petugas', $p->nama_lengkap)
                ->sum('ttl_trkasir');

            $komisiPetugas = $totalPenjualan * ($persen / 100);
            $totalKomisi += $komisiPetugas;

            $rows[] = [
                'nama_lengkap' => $p->nama_lengkap,
                'komisi' => $komisiPetugas,
            ];
        }

        return view('inventory.komisi.global', [
            'judul' => 'Inventory',
            'persenAktif' => $persen,
            'statusAktif' => $aktif->status ?? 'OFF',
            'namaBulan' => $this->bulanIndo[(int) now()->format('n')],
            'rows' => $rows,
            'totalKomisi' => $totalKomisi,
        ]);
    }

    /**
     * Simpan konfigurasi komisi global, mengikuti act=input_komisiglobal.
     * Satu baris per bulan+tahun; status ON dipastikan cuma satu yang aktif.
     */
    public function globalStore(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $validated = $request->validate([
            'nilai' => 'required|numeric|min:0',
            'status' => 'required|in:ON,OFF',
        ]);

        $hariIni = now();
        $admin = Auth::guard('admin')->user();

        $existing = KomisiGlobal::whereYear('tgl', $hariIni->year)
            ->whereMonth('tgl', $hariIni->month)
            ->first();

        if ($existing) {
            $existing->update([
                'nilai' => $validated['nilai'],
                'tgl' => $hariIni->toDateString(),
                'petugas' => $admin->nama_lengkap,
                'status' => $validated['status'],
            ]);
        } else {
            $existing = KomisiGlobal::create([
                'nilai' => $validated['nilai'],
                'tgl' => $hariIni->toDateString(),
                'petugas' => $admin->nama_lengkap,
                'status' => $validated['status'],
            ]);
        }

        if ($validated['status'] === 'ON') {
            KomisiGlobal::where('id_komisiglobal', '!=', $existing->id_komisiglobal)
                ->update(['status' => 'OFF']);
        }

        return redirect()->route('inventory.komisi.global')->with('success', 'Komisi global berhasil disimpan.');
    }

    /**
     * Form pilih bulan untuk history, mengikuti case 'history'. Hanya pemilik.
     */
    public function history()
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        return view('inventory.komisi.history', [
            'judul' => 'Inventory',
            'bulanIndo' => $this->bulanIndo,
        ]);
    }

    /**
     * Hasil history per bulan, mengikuti case 'bulan'. Bug tahun hardcode di legacy
     * sudah diperbaiki: pakai tahun berjalan, bukan '2025' yang ditulis mati.
     */
    public function historyResult(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $validated = $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        $bulan = (int) $validated['bulan'];
        $tahun = now()->year;

        $global = KomisiGlobal::whereYear('tgl', $tahun)->whereMonth('tgl', $bulan)->first();
        $persen = $global->nilai ?? 0;

        $petugasList = DB::table('admin')->where('akses_level', 'petugas')->get(['id_admin', 'nama_lengkap']);

        $rows = [];
        foreach ($petugasList as $p) {
            $totalPenjualan = (float) DB::table('trkasir')
                ->whereMonth('tgl_trkasir', $bulan)
                ->whereYear('tgl_trkasir', $tahun)
                ->where('petugas', $p->nama_lengkap)
                ->sum('ttl_trkasir');

            $rows[] = [
                'nama_lengkap' => $p->nama_lengkap,
                'komisi' => $totalPenjualan * ($persen / 100),
            ];
        }

        return view('inventory.komisi.bulan', [
            'judul' => 'Inventory',
            'namaBulan' => $this->bulanIndo[$bulan],
            'tahun' => $tahun,
            'rows' => $rows,
        ]);
    }
}
