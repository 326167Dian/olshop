<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Pto;
use App\Models\Setheader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryPtoController extends Controller
{
    /**
     * Modul "PTO" (Pemantauan Terapi Obat), mengikuti
     * public/apotekberlian/masuk/modul/mod_pemantauan_terapi_obat/pto.php.
     * Tidak digerbang flag admin manapun di legacy — semua admin yang login bisa
     * lihat & input, tapi edit/hapus khusus pemilik (sama seperti legacy).
     */
    public function index()
    {
        $pto = Pto::with('pelanggan')->orderByDesc('id_pto')->get();

        return view('inventory.pto.index', [
            'judul' => 'Inventory',
            'pto' => $pto,
            'isPemilik' => Auth::guard('admin')->user()->isPemilik(),
        ]);
    }

    public function riwayat(Request $request)
    {
        $idPelanggan = (int) $request->query('id_pelanggan', 0);
        $pelanggan = Pelanggan::find($idPelanggan);

        if (!$pelanggan) {
            return redirect()->route('inventory.pelanggan.index')->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $tglAwal = $request->query('tgl_awal', '');
        $tglAkhir = $request->query('tgl_akhir', '');

        $query = Pto::where('id_pelanggan', $idPelanggan);
        if ($tglAwal) {
            $query->whereRaw('COALESCE(tanggal_1, DATE(created_at)) >= ?', [$tglAwal]);
        }
        if ($tglAkhir) {
            $query->whereRaw('COALESCE(tanggal_1, DATE(created_at)) <= ?', [$tglAkhir]);
        }

        return view('inventory.pto.riwayat', [
            'judul' => 'Inventory',
            'pelanggan' => $pelanggan,
            'riwayat' => $query->orderByDesc('id_pto')->get(),
            'tglAwal' => $tglAwal,
            'tglAkhir' => $tglAkhir,
            'isPemilik' => Auth::guard('admin')->user()->isPemilik(),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $idPelanggan = (int) $request->query('id_pelanggan', 0);
        $pelanggan = Pelanggan::findOrFail($idPelanggan);

        $tglAwal = $request->query('tgl_awal', '');
        $tglAkhir = $request->query('tgl_akhir', '');

        $query = Pto::where('id_pelanggan', $idPelanggan);
        if ($tglAwal) {
            $query->whereRaw('COALESCE(tanggal_1, DATE(created_at)) >= ?', [$tglAwal]);
        }
        if ($tglAkhir) {
            $query->whereRaw('COALESCE(tanggal_1, DATE(created_at)) <= ?', [$tglAkhir]);
        }

        return view('inventory.pto.export', [
            'pelanggan' => $pelanggan,
            'riwayat' => $query->orderByDesc('id_pto')->get(),
            'tglAwal' => $tglAwal,
            'tglAkhir' => $tglAkhir,
        ]);
    }

    public function create(Request $request)
    {
        $idPelanggan = (int) $request->query('id_pelanggan', 0);
        $pelanggan = Pelanggan::find($idPelanggan);

        if (!$pelanggan) {
            return redirect()->route('inventory.pelanggan.index')->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $umur = $pelanggan->tanggal_lahir ? \Carbon\Carbon::parse($pelanggan->tanggal_lahir)->age . ' tahun' : '';

        return view('inventory.pto.create', [
            'judul' => 'Inventory',
            'pelanggan' => $pelanggan,
            'umur' => $umur,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|integer|exists:pelanggan,id_pelanggan',
            'nm_pelanggan' => 'required|string|max:120',
            'jenis_kelamin' => 'nullable|string|max:30',
            'umur' => 'nullable|string|max:30',
            'alamat_pelanggan' => 'nullable|string',
            'tlp_pelanggan' => 'nullable|string|max:30',
            'tanggal_1' => 'nullable|date',
            'catatan_1' => 'nullable|string',
            'obat_1' => 'nullable|string',
            'masalah_1' => 'nullable|string',
            'tindak_1' => 'nullable|string',
            'tanggal_2' => 'nullable|date',
            'catatan_2' => 'nullable|string',
            'obat_2' => 'nullable|string',
            'masalah_2' => 'nullable|string',
            'tindak_2' => 'nullable|string',
            'tempat_ttd' => 'nullable|string|max:120',
            'tanggal_ttd' => 'nullable|date',
        ]);

        $pto = Pto::create(array_merge($validated, [
            'created_by' => Auth::guard('admin')->user()->nama_lengkap,
        ]));

        return redirect()->route('inventory.pto.show', $pto->id_pto)->with('success', 'Data PTO berhasil disimpan.');
    }

    public function show(Pto $pto)
    {
        return view('inventory.pto.show', [
            'pto' => $pto,
        ]);
    }

    public function edit(Pto $pto)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403, 'Fitur edit PTO hanya untuk pemilik.');

        return view('inventory.pto.edit', [
            'judul' => 'Inventory',
            'pto' => $pto,
        ]);
    }

    public function update(Request $request, Pto $pto)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403, 'Fitur edit PTO hanya untuk pemilik.');

        $validated = $request->validate([
            'nm_pelanggan' => 'required|string|max:120',
            'jenis_kelamin' => 'nullable|string|max:30',
            'umur' => 'nullable|string|max:30',
            'alamat_pelanggan' => 'nullable|string',
            'tlp_pelanggan' => 'nullable|string|max:30',
            'tanggal_1' => 'nullable|date',
            'catatan_1' => 'nullable|string',
            'obat_1' => 'nullable|string',
            'masalah_1' => 'nullable|string',
            'tindak_1' => 'nullable|string',
            'tanggal_2' => 'nullable|date',
            'catatan_2' => 'nullable|string',
            'obat_2' => 'nullable|string',
            'masalah_2' => 'nullable|string',
            'tindak_2' => 'nullable|string',
            'tempat_ttd' => 'nullable|string|max:120',
            'tanggal_ttd' => 'nullable|date',
        ]);

        $pto->update($validated);

        return redirect()->route('inventory.pto.show', $pto->id_pto)->with('success', 'Data PTO berhasil diperbarui.');
    }

    public function destroy(Pto $pto)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403, 'Fitur hapus PTO hanya untuk pemilik.');

        $idPelanggan = $pto->id_pelanggan;
        $pto->delete();

        return redirect()->route('inventory.pto.riwayat', ['id_pelanggan' => $idPelanggan])
            ->with('success', 'Data PTO berhasil dihapus.');
    }
}
