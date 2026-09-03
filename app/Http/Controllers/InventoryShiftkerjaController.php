<?php

namespace App\Http\Controllers;

use App\Models\CaraBayar;
use App\Models\NamaShift;
use App\Models\Setheader;
use App\Models\WaktuKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryShiftkerjaController extends Controller
{
    /**
     * Modul "Buka/Tutup Kasir" (module=shiftkerja), mengikuti
     * public/apotekberlian/masuk/modul/mod_shiftkerja/*.php.
     *
     * Log buka/tutup kasir per shift (tabel `waktukerja`, dua shift tetap: Pagi/Sore dari
     * `namashift`) -- satu shift 'ON' per hari (dicek sebelum insert baru), ditutup dengan
     * mengisi saldo akhir. "Koreksi" (edit manual semua kolom) dan "Hapus" khusus pemilik,
     * mengikuti legacy's `if ($_SESSION['level'] == 'pemilik')` di shift_serverside.php.
     * "Laporan" (ringkasan kas per shift, dari tabel `trkasir` milik modul Penjualan/Kasir
     * yang belum dibuat -- dibaca lewat DB::table() saja tanpa model, sama seperti pola
     * laporan "hutang" di modul Supplier sebelum modul trbmasuk ada) terbuka untuk semua
     * role. FPDF struk thermal diganti HTML cetak-browser (@page 80mm auto), sesuai
     * konvensi print di seluruh port ini -- tidak ada library PDF di mana pun.
     *
     * Perbaikan dari legacy: case 'edit' (Tutup Kasir) membaca `$_GET['id']` tapi hasilnya
     * langsung ditimpa oleh query kedua (WHERE tanggal=hari ini AND status='ON') sehingga
     * parameter id itu sama sekali tidak dipakai -- di sini closeForm()/close() memang
     * sengaja tidak butuh id, langsung mencari shift 'ON' hari ini di server (bukan
     * dipercayakan dari input tersembunyi form seperti legacy, mengurangi risiko baris yang
     * salah ikut tertutup kalau ada manipulasi form).
     */
    public function index()
    {
        return view('inventory.shiftkerja.index', ['judul' => 'Inventory']);
    }

    public function data()
    {
        $query = WaktuKerja::query()
            ->leftJoin('namashift', 'namashift.shift', '=', 'waktukerja.shift')
            ->orderByDesc('waktukerja.id_shift')
            ->select(['waktukerja.*', 'namashift.nama_shift']);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('saldoawal', fn ($row) => number_format($row->saldoawal, 0, ',', '.'))
            ->editColumn('saldoakhir', fn ($row) => number_format($row->saldoakhir, 0, ',', '.'))
            ->addColumn('aksi', function ($row) {
                $isPemilik = Auth::guard('admin')->user()->isPemilik();
                $btn = '<div class="dropdown">'
                    . '<button class="btn btn-default btn-xs dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>'
                    . '<ul class="dropdown-menu">';

                if ($isPemilik) {
                    $btn .= '<li><a class="dropdown-item" href="' . route('inventory.shiftkerja.koreksi.form', $row->id_shift) . '">Koreksi</a></li>';
                    $btn .= '<li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="hapusShift(' . $row->id_shift . ')">Hapus</a></li>';
                }

                $btn .= '<li><a class="dropdown-item" target="_blank" href="' . route('inventory.shiftkerja.laporan', $row->id_shift) . '">Laporan</a></li>';
                $btn .= '</ul></div>';

                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Form Buka Kasir, mengikuti case 'tambah'.
     */
    public function create()
    {
        $sudahOn = WaktuKerja::where('tanggal', now()->toDateString())->where('status', 'ON')->exists();

        return view('inventory.shiftkerja.buka', [
            'judul' => 'Inventory',
            'sudahOn' => $sudahOn,
            'shiftList' => NamaShift::orderBy('shift')->get(),
        ]);
    }

    /**
     * Simpan Buka Kasir, mengikuti act=input_shiftkerja.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift' => 'required|integer',
            'saldoawal' => 'required|numeric|min:0',
        ]);

        $tanggal = now()->toDateString();
        abort_if(
            WaktuKerja::where('tanggal', $tanggal)->where('status', 'ON')->exists(),
            422,
            'Kasir sudah dibuka!'
        );

        WaktuKerja::create([
            'petugasbuka' => Auth::guard('admin')->user()->nama_lengkap,
            'petugastutup' => '',
            'tanggal' => $tanggal,
            'waktubuka' => now()->toTimeString(),
            'waktututup' => '00:00:00',
            'shift' => $validated['shift'],
            'saldoawal' => $validated['saldoawal'],
            'saldoakhir' => 0,
            'status' => 'ON',
        ]);

        return redirect()->route('inventory.shiftkerja.index')->with('success', 'Kasir berhasil dibuka.');
    }

    /**
     * Form Tutup Kasir, mengikuti case 'edit'.
     */
    public function closeForm()
    {
        $waktuKerja = WaktuKerja::where('tanggal', now()->toDateString())->where('status', 'ON')->first();
        abort_if(!$waktuKerja, 422, 'Kasir sudah ditutup!');

        return view('inventory.shiftkerja.tutup', [
            'judul' => 'Inventory',
            'waktuKerja' => $waktuKerja,
        ]);
    }

    /**
     * Simpan Tutup Kasir, mengikuti act=update_waktukerja.
     */
    public function close(Request $request)
    {
        $validated = $request->validate([
            'saldoakhir' => 'required|numeric|min:0',
        ]);

        $waktuKerja = WaktuKerja::where('tanggal', now()->toDateString())->where('status', 'ON')->first();
        abort_if(!$waktuKerja, 422, 'Kasir sudah ditutup!');

        $waktuKerja->update([
            'petugastutup' => Auth::guard('admin')->user()->nama_lengkap,
            'waktututup' => now()->toTimeString(),
            'status' => 'OFF',
            'saldoakhir' => $validated['saldoakhir'],
        ]);

        return redirect()->route('inventory.shiftkerja.index')->with('success', 'Kasir berhasil ditutup.');
    }

    /**
     * Form Koreksi (edit manual semua kolom), mengikuti case 'editkoreksi'. KHUSUS pemilik.
     */
    public function koreksiForm(WaktuKerja $waktuKerja)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        return view('inventory.shiftkerja.koreksi', [
            'judul' => 'Inventory',
            'waktuKerja' => $waktuKerja,
            'shiftList' => NamaShift::orderBy('shift')->get(),
            'petugasList' => DB::table('admin')->orderBy('nama_lengkap')->pluck('nama_lengkap'),
        ]);
    }

    /**
     * Simpan Koreksi, mengikuti act=update_waktukerjakoreksi. KHUSUS pemilik.
     */
    public function koreksi(Request $request, WaktuKerja $waktuKerja)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $validated = $request->validate([
            'petugasbuka' => 'required|string|max:100',
            'petugastutup' => 'nullable|string|max:100',
            'shift' => 'required|integer',
            'tanggal' => 'required|date',
            'waktubuka' => 'required',
            'waktututup' => 'nullable',
            'saldoawal' => 'required|numeric|min:0',
            'saldoakhir' => 'required|numeric|min:0',
            'status' => 'required|in:ON,OFF',
        ]);

        $validated['petugastutup'] = $validated['petugastutup'] ?? '';
        $validated['waktututup'] = $validated['waktututup'] ?: '00:00:00';

        $waktuKerja->update($validated);

        return redirect()->route('inventory.shiftkerja.index')->with('success', 'Data shift berhasil dikoreksi.');
    }

    /**
     * Hapus baris shift, mengikuti act=hapus. KHUSUS pemilik.
     */
    public function destroy(WaktuKerja $waktuKerja)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $waktuKerja->delete();

        return redirect()->route('inventory.shiftkerja.index')->with('success', 'Data shift berhasil dihapus.');
    }

    /**
     * Ringkasan kas per shift (struk cetak), mengikuti laporanshiftday.php. Terbuka untuk
     * semua role yang punya akses modul ini.
     */
    public function laporan(WaktuKerja $waktuKerja)
    {
        $totalPenjualan = (float) DB::table('trkasir')
            ->where('shift', $waktuKerja->shift)
            ->where('tgl_trkasir', $waktuKerja->tanggal)
            ->sum('ttl_trkasir');

        $jumlahTransaksi = DB::table('trkasir')
            ->where('shift', $waktuKerja->shift)
            ->where('tgl_trkasir', $waktuKerja->tanggal)
            ->count();

        $perCaraBayar = CaraBayar::orderBy('urutan')->get()->map(function ($cb) use ($waktuKerja) {
            $cb->total = (float) DB::table('trkasir')
                ->where('shift', $waktuKerja->shift)
                ->where('tgl_trkasir', $waktuKerja->tanggal)
                ->where('id_carabayar', $cb->id_carabayar)
                ->sum('ttl_trkasir');

            return $cb;
        });

        return view('inventory.shiftkerja.laporan', [
            'judul' => 'Inventory',
            'waktuKerja' => $waktuKerja,
            'totalPenjualan' => $totalPenjualan,
            'jumlahTransaksi' => $jumlahTransaksi,
            'perCaraBayar' => $perCaraBayar,
            'setheader' => Setheader::first(),
        ]);
    }
}
