<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\JenisJurnal;
use App\Models\JurnalKas;
use App\Models\Kas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryJurnalkasController extends Controller
{
    /**
     * Modul "Jurnal Kas" (grup Inventory, flag admin `jurnalkas`), mengikuti
     * `mod_jurnalkas/jurnalkas.php` (index/tambah/tambah2/edit/jenistransaksi/add/
     * ubah/kemarin/pilihhari/tampil2/rekap/tampil3/detail) + `aksi_jurnalkas.php`
     * (input_jurnal/input_jurnal2/update_jurnal/hapus) + `aksi_jenistransaksi.php`
     * (CRUD kategori transaksi) + `mod_laporan/cetak_jurnal_excel.php`. Modul
     * pembukuan kas sederhana: setiap baris jurnal adalah SATU pengeluaran (debit)
     * ATAU SATU pemasukan (kredit), dikelompokkan per `jenis_jurnal`, dengan saldo
     * kas tunggal (`kas`, tabel 1 baris) yang diperbarui setiap ada perubahan.
     *
     * `act=tampil` (menampilkan `$r['deskripsi']`) TIDAK diporting -- kolom
     * `deskripsi` TIDAK ADA di tabel `jurnal` (kolomnya `ket`), dan tidak ada satu
     * pun link di `jurnalkas.php` sendiri yang menuju act ini (beda dari Catatan
     * yang punya tombol TAMPIL asli) -- dikonfirmasi dead code sekaligus rusak,
     * bukan diabaikan begitu saja. `mod_jurnalkas/sinkronisasi.php` dan
     * `tampil_jurnal.php` juga dikonfirmasi tidak direferensikan di mana pun.
     *
     * **`kas.saldo` diporting sebagai HASIL HITUNG ULANG PENUH
     * (`SUM(kredit)-SUM(debit)`) setiap kali ada perubahan, BUKAN penyesuaian
     * bertahap seperti sebagian aksi legacy.** Legacy sendiri TIDAK KONSISTEN:
     * `input_jurnal`/`input_jurnal2`/`hapus` menyesuaikan saldo secara bertahap
     * (+/- nilai baris), sedangkan `update_jurnal` justru menghitung ulang penuh.
     * Cara bertahap `hapus` juga rapuh terhadap satu celah nyata: form edit
     * legacy mengizinkan MENGISI debit DAN kredit sekaligus pada satu baris (tidak
     * ada validasi saling eksklusif) -- kalau baris seperti itu lalu dihapus,
     * `hapus` HANYA membalik salah satu sisi (`if($r['debit']>0)` -- sisi lainnya
     * diam-diam terlewat). Karena `kas.saldo` adalah angka finansial yang
     * ditampilkan besar-besar ke pemilik, SEMUA aksi (`store`/`update`/`destroy`)
     * di sini memakai satu helper `recomputeSaldo()` yang sama, menghitung ulang
     * dari nol setiap kali -- tidak mungkin ngedrift, apa pun kombinasi datanya.
     *
     * **Perbaikan keamanan konsisten dengan pola yang sudah diterapkan berulang
     * kali sepanjang port ini:** beberapa sub-halaman (`kemarin`, `pilihhari`/
     * `tampil2`, `rekap`/`tampil3`/`detail`, export Excel) HANYA disembunyikan
     * dari navigasi untuk non-pemilik di legacy -- act/endpoint-nya sendiri sama
     * sekali tidak mengecek `$_SESSION['level']`, jadi siapa pun yang login bisa
     * langsung membuka URL-nya. Export Excel malah PALING longgar: filenya tidak
     * mengecek sesi sama sekali (bukan cuma level, bahkan status login pun tidak
     * dicek) padahal isinya seluruh riwayat jurnal kas. Semua endpoint ini
     * digerbang `abort_unless(isPemilik())` di server di sini, bukan cuma
     * disembunyikan di tombol. `update_jurnal`/`hapus` (jurnal) SUDAH benar
     * mengecek "petugas sama ATAU pemilik" di endpoint aksinya sendiri --
     * diporting apa adanya (bukan bug, ini sudah benar dari awal).
     *
     * `jenis_jurnal.hapus` legacy mengecek `$r['petugas']` padahal tabel
     * `jenis_jurnal` TIDAK PUNYA kolom `petugas` sama sekali -- perbandingan itu
     * akan selalu gagal, jadi hasil akhirnya SELALU pemilik-only, meski ditulis
     * seolah ada jalur "petugas yang sama". Diporting sebagai gerbang pemilik-only
     * langsung (perilaku yang teramati sama persis, ditulis dengan jujur bukan
     * kebetulan).
     *
     * **Navigasi disederhanakan jadi SATU partial dengan aturan konsisten**
     * (Jenis Transaksi/Pilih Hari/Catatan Kemarin/Rekapitulasi semuanya pemilik-only)
     * -- legacy sendiri tidak konsisten antar halaman (halaman index menyembunyikan
     * keempatnya untuk non-pemilik, halaman "tambah" menampilkan Jenis
     * Transaksi/Pilih Hari ke semua orang dan hanya menyembunyikan Catatan
     * Kemarin) -- ini murni beda tampilan navigasi antar file, bukan keputusan
     * keamanan (gerbang server-side di atas sudah konsisten & benar untuk
     * semuanya), jadi disatukan tanpa mengubah apa pun yang sifatnya fungsional.
     *
     * **Bug format tanggal diperbaiki, tidak direplikasi** (kelas bug yang sama
     * ditemukan berulang di Stok Opname Bulanan/Harian): kolom `current`
     * (datetime asli) ditulis `date('ymdHis')` (string tak valid) -- diperbaiki
     * jadi `now()`.
     *
     * **Optimasi N+1 diterapkan:** `tampil3` (rekap per jenis) legacy menjalankan
     * 1 query tambahan PER jenis transaksi di dalam loop -- diganti 1 query
     * `GROUP BY idjenis`. Export Excel legacy melakukan 1 query `jenis_jurnal`
     * TERPISAH per baris jurnal (TANPA filter tanggal -- dump SELURUH riwayat)
     * -- diganti 1 query JOIN, format HTML/CSS `.xls` (konsisten dengan seluruh
     * export lain di port ini, bukan PhpSpreadsheet).
     */
    public function index()
    {
        $tgl = now()->toDateString();
        $rows = JurnalKas::with('jenis')->where('tanggal', $tgl)->orderByDesc('id_jurnal')->get();
        $totals = $this->hitungTotalRentang($tgl, $tgl);
        $kas = Kas::find(1);
        $admin = Auth::guard('admin')->user();

        return view('inventory.jurnalkas.index', [
            'judul' => 'Inventory',
            'rows' => $rows,
            'totals' => $totals,
            'saldo' => $kas->saldo ?? 0,
            'isPemilik' => $admin->isPemilik(),
        ]);
    }

    public function create()
    {
        return view('inventory.jurnalkas.form', [
            'judul' => 'Inventory',
            'tipe' => 1,
            'jenisList' => JenisJurnal::where('tipe', 1)->get(),
        ]);
    }

    public function createIncome()
    {
        return view('inventory.jurnalkas.form', [
            'judul' => 'Inventory',
            'tipe' => 2,
            'jenisList' => JenisJurnal::where('tipe', 2)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'idjenis' => 'required|integer|exists:jenis_jurnal,idjenis',
            'ket' => 'required|string',
            'carabayar' => 'required|in:TUNAI,TRANSFER',
            'nilai' => 'required|numeric|min:0',
            'tipe' => 'required|in:1,2',
        ]);

        $admin = Auth::guard('admin')->user();

        JurnalKas::create([
            'tanggal' => now()->toDateString(),
            'ket' => $validated['ket'],
            'petugas' => $admin->nama_lengkap,
            'idjenis' => $validated['idjenis'],
            'debit' => $validated['tipe'] == 1 ? $validated['nilai'] : 0,
            'kredit' => $validated['tipe'] == 2 ? $validated['nilai'] : 0,
            'carabayar' => $validated['carabayar'],
            'current' => now(),
        ]);

        $this->recomputeSaldo();

        return redirect()->route('inventory.jurnalkas.index')->with('success', 'Jurnal berhasil ditambahkan.');
    }

    public function edit(JurnalKas $jurnal)
    {
        $admin = Auth::guard('admin')->user();
        if (!$this->bolehUbah($jurnal, $admin)) {
            return redirect()->route('inventory.jurnalkas.index')->with('error', 'Jurnal hanya bisa diedit orang yang sama atau pemilik apotek!');
        }

        return view('inventory.jurnalkas.edit', [
            'judul' => 'Inventory',
            'jurnal' => $jurnal,
            'jenisList' => JenisJurnal::orderByDesc('idjenis')->get(),
        ]);
    }

    public function update(Request $request, JurnalKas $jurnal)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($this->bolehUbah($jurnal, $admin), 403, 'Jurnal hanya bisa diedit orang yang sama atau pemilik apotek!');

        $validated = $request->validate([
            'idjenis' => 'required|integer|exists:jenis_jurnal,idjenis',
            'ket' => 'required|string',
            'carabayar' => 'required|in:TUNAI,TRANSFER',
            'debit' => 'required|numeric|min:0',
            'kredit' => 'required|numeric|min:0',
        ]);

        $jurnal->update([
            'ket' => $validated['ket'],
            'idjenis' => $validated['idjenis'],
            'carabayar' => $validated['carabayar'],
            'debit' => $validated['debit'],
            'kredit' => $validated['kredit'],
            'current' => now(),
        ]);

        $this->recomputeSaldo();

        return redirect()->route('inventory.jurnalkas.index')->with('success', 'Jurnal berhasil diubah.');
    }

    public function destroy(JurnalKas $jurnal)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($this->bolehUbah($jurnal, $admin), 403, 'Jurnal hanya bisa dihapus orang yang sama atau pemilik apotek!');

        $jurnal->delete();
        $this->recomputeSaldo();

        return redirect()->route('inventory.jurnalkas.index')->with('success', 'Jurnal berhasil dihapus.');
    }

    /** Ports act=jenistransaksi. */
    public function jenisIndex()
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $rows = JenisJurnal::orderBy('idjenis')->get();

        return view('inventory.jurnalkas.jenis-index', [
            'judul' => 'Inventory',
            'rows' => $rows,
        ]);
    }

    public function jenisCreate()
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        return view('inventory.jurnalkas.jenis-create', ['judul' => 'Inventory']);
    }

    public function jenisStore(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $validated = $request->validate([
            'nm_jurnal' => 'required|string|max:100',
            'tipe' => 'required|in:1,2',
        ]);

        if (JenisJurnal::where('nm_jurnal', $validated['nm_jurnal'])->exists()) {
            return back()->withInput()->withErrors(['nm_jurnal' => 'Jenis Transaksi sudah ada!']);
        }

        JenisJurnal::create($validated);

        return redirect()->route('inventory.jurnalkas.jenis.index')->with('success', 'Jenis transaksi berhasil ditambahkan.');
    }

    public function jenisEdit(JenisJurnal $jenisJurnal)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        return view('inventory.jurnalkas.jenis-edit', ['judul' => 'Inventory', 'jenisJurnal' => $jenisJurnal]);
    }

    public function jenisUpdate(Request $request, JenisJurnal $jenisJurnal)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $validated = $request->validate(['nm_jurnal' => 'required|string|max:100']);
        $jenisJurnal->update($validated);

        return redirect()->route('inventory.jurnalkas.jenis.index')->with('success', 'Jenis transaksi berhasil diubah.');
    }

    /** Ports jenis_jurnal hapus -- lihat catatan kelas soal kolom petugas yang tak ada. */
    public function jenisDestroy(JenisJurnal $jenisJurnal)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403, 'Jurnal kas harus dihapus orang yang sama atau pemilik apotek!');

        $jenisJurnal->delete();

        return redirect()->route('inventory.jurnalkas.jenis.index')->with('success', 'Jenis transaksi berhasil dihapus.');
    }

    /** Ports act=kemarin -- pemilik-only server-side, lihat catatan kelas. */
    public function kemarin()
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $tglAkhir = now()->copy()->subDay()->toDateString();
        $tglAwal = now()->copy()->subDays(180)->toDateString();

        $rows = JurnalKas::with('jenis')->whereBetween('tanggal', [$tglAwal, $tglAkhir])->orderByDesc('id_jurnal')->get();

        return view('inventory.jurnalkas.kemarin', ['judul' => 'Inventory', 'rows' => $rows]);
    }

    public function pilihHari()
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        return view('inventory.jurnalkas.pilih-hari', ['judul' => 'Inventory']);
    }

    /** Ports act=tampil2. */
    public function tampilRange(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $rows = JurnalKas::with('jenis')
            ->whereBetween('tanggal', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->orderByDesc('id_jurnal')
            ->get();

        $totals = $this->hitungTotalRentang($validated['tgl_awal'], $validated['tgl_akhir']);

        return view('inventory.jurnalkas.tampil-range', [
            'judul' => 'Inventory',
            'rows' => $rows,
            'totals' => $totals,
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
        ]);
    }

    public function rekapForm()
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        return view('inventory.jurnalkas.rekap-form', ['judul' => 'Inventory']);
    }

    /** Ports act=tampil3 -- lihat catatan kelas soal optimasi N+1. */
    public function rekapResult(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $rows = JenisJurnal::query()
            ->leftJoin('jurnal', function ($join) use ($validated) {
                $join->on('jurnal.idjenis', '=', 'jenis_jurnal.idjenis')
                    ->whereBetween('jurnal.tanggal', [$validated['tgl_awal'], $validated['tgl_akhir']]);
            })
            ->groupBy('jenis_jurnal.idjenis', 'jenis_jurnal.nm_jurnal')
            ->orderByDesc('jenis_jurnal.idjenis')
            ->selectRaw('jenis_jurnal.idjenis, jenis_jurnal.nm_jurnal, COALESCE(SUM(jurnal.debit),0) as debit, COALESCE(SUM(jurnal.kredit),0) as kredit')
            ->get();

        return view('inventory.jurnalkas.rekap-result', [
            'judul' => 'Inventory',
            'rows' => $rows,
            'totalDebit' => $rows->sum('debit'),
            'totalKredit' => $rows->sum('kredit'),
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
        ]);
    }

    /** Ports act=detail (drill-down rekap per jenis). */
    public function rekapDetail(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $validated = $request->validate([
            'id' => 'required|integer|exists:jenis_jurnal,idjenis',
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $jenis = JenisJurnal::find($validated['id']);
        $rows = JurnalKas::where('idjenis', $validated['id'])
            ->whereBetween('tanggal', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->get();

        return view('inventory.jurnalkas.rekap-detail', [
            'judul' => 'Inventory',
            'jenis' => $jenis,
            'rows' => $rows,
            'totalDebit' => $rows->sum('debit'),
            'totalKredit' => $rows->sum('kredit'),
        ]);
    }

    /** Ports cetak_jurnal_excel.php -- lihat catatan kelas soal optimasi & gerbang pemilik. */
    public function excel()
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $rows = JurnalKas::with('jenis')->orderBy('id_jurnal')->get();

        return response()->view('inventory.jurnalkas.excel', [
            'rows' => $rows,
            'printedBy' => $admin->nama_lengkap ?? '',
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="Jurnal_Kas.xls"',
        ]);
    }

    private function hitungTotalRentang(string $tglAwal, string $tglAkhir): array
    {
        $agg = function (string $carabayar) use ($tglAwal, $tglAkhir) {
            return JurnalKas::whereBetween('tanggal', [$tglAwal, $tglAkhir])
                ->where('carabayar', $carabayar)
                ->selectRaw('COALESCE(SUM(debit),0) as debit, COALESCE(SUM(kredit),0) as kredit')
                ->first();
        };

        $tunai = $agg('TUNAI');
        $transfer = $agg('TRANSFER');

        $totalDebit = (float) $tunai->debit + (float) $transfer->debit;
        $totalKredit = (float) $tunai->kredit + (float) $transfer->kredit;

        return [
            'pengeluaranTunai' => (float) $tunai->debit,
            'pengeluaranTransfer' => (float) $transfer->debit,
            'pemasukanTunai' => (float) $tunai->kredit,
            'pemasukanTransfer' => (float) $transfer->kredit,
            'saldoTunai' => (float) $tunai->kredit - (float) $tunai->debit,
            'saldoTransfer' => (float) $transfer->kredit - (float) $transfer->debit,
            'totalDebit' => $totalDebit,
            'totalKredit' => $totalKredit,
            'totalSaldo' => $totalKredit - $totalDebit,
        ];
    }

    private function recomputeSaldo(): void
    {
        $totals = JurnalKas::selectRaw('COALESCE(SUM(kredit),0) as kr, COALESCE(SUM(debit),0) as db')->first();
        Kas::where('id_kas', 1)->update(['saldo' => (float) $totals->kr - (float) $totals->db]);
    }

    private function bolehUbah(JurnalKas $jurnal, Admin $admin): bool
    {
        return trim((string) $jurnal->petugas) === trim((string) $admin->nama_lengkap) || $admin->isPemilik();
    }
}
