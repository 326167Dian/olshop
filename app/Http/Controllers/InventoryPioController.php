<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Pio;
use App\Models\Setheader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryPioController extends Controller
{
    /**
     * Modul "PIO" (Pelayanan Informasi Obat), mengikuti
     * public/apotekberlian/masuk/modul/mod_pio/pio.php.
     * Tidak digerbang flag admin manapun di legacy — semua admin yang login bisa akses.
     */
    public function index()
    {
        $pio = Pio::with('pelanggan')->orderByDesc('id_pio')->get();

        return view('inventory.pio.index', [
            'judul' => 'Inventory',
            'pio' => $pio,
        ]);
    }

    public function create(Request $request)
    {
        $idPelanggan = (int) $request->query('id_pelanggan', 0);
        $pelanggan = $idPelanggan > 0 ? Pelanggan::find($idPelanggan) : null;

        $umur = '';
        if ($pelanggan && $pelanggan->tanggal_lahir) {
            $umur = \Carbon\Carbon::parse($pelanggan->tanggal_lahir)->age;
        }

        return view('inventory.pio.create', [
            'judul' => 'Inventory',
            'pelanggan' => $pelanggan,
            'idPelanggan' => $idPelanggan,
            'umur' => $umur,
            'noPio' => $this->nextNoPio(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|integer|exists:pelanggan,id_pelanggan',
            'no_pio' => 'required|string|max:50',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'metode' => 'required|in:Lisan,Tertulis,Telepon',
            'nama_penanya' => 'required|string|max:255',
            'no_telp_penanya' => 'nullable|string|max:50',
            'status_penanya' => 'required|in:Pasien,Keluarga Pasien,Petugas Kesehatan',
            'status_penanya_ket' => 'nullable|string|max:255',
            'umur_pasien' => 'nullable|integer',
            'tinggi_pasien' => 'nullable|integer',
            'berat_pasien' => 'nullable|integer',
            'jenis_kelamin' => 'nullable|in:L,P',
            'kehamilan_minggu' => 'nullable|integer',
            'uraian_pertanyaan' => 'required|string',
            'jenis_pertanyaan_lain_lain_ket' => 'nullable|string|max:255',
            'jawaban' => 'nullable|string',
            'referensi' => 'nullable|string',
            'penyampaian_jawaban' => 'nullable|in:Segera,Dalam 24 jam,Lebih dari 24 jam',
            'apoteker_penjawab' => 'nullable|string|max:255',
            'tanggal_jawab' => 'nullable|date',
            'waktu_jawab' => 'nullable',
            'metode_jawab' => 'nullable|in:Lisan,Tertulis,Telepon',
        ]);

        $validated = array_merge($validated, $this->flagsFromRequest($request), [
            'kehamilan' => $request->boolean('kehamilan'),
            'menyusui' => $request->boolean('menyusui'),
            'created_by' => Auth::guard('admin')->user()->nama_lengkap,
        ]);

        Pio::create($validated);

        return redirect()->route('inventory.pio.index')->with('success', 'Data PIO berhasil disimpan.');
    }

    public function show(Pio $pio)
    {
        $pio->load('pelanggan');

        return view('inventory.pio.show', [
            'pio' => $pio,
            'setheader' => Setheader::first(),
        ]);
    }

    public function edit(Pio $pio)
    {
        $pio->load('pelanggan');

        return view('inventory.pio.edit', [
            'judul' => 'Inventory',
            'pio' => $pio,
        ]);
    }

    public function update(Request $request, Pio $pio)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'metode' => 'required|in:Lisan,Tertulis,Telepon',
            'nama_penanya' => 'required|string|max:255',
            'no_telp_penanya' => 'nullable|string|max:50',
            'status_penanya' => 'required|in:Pasien,Keluarga Pasien,Petugas Kesehatan',
            'status_penanya_ket' => 'nullable|string|max:255',
            'umur_pasien' => 'nullable|integer',
            'tinggi_pasien' => 'nullable|integer',
            'berat_pasien' => 'nullable|integer',
            'jenis_kelamin' => 'nullable|in:L,P',
            'kehamilan_minggu' => 'nullable|integer',
            'uraian_pertanyaan' => 'required|string',
            'jenis_pertanyaan_lain_lain_ket' => 'nullable|string|max:255',
            'jawaban' => 'nullable|string',
            'referensi' => 'nullable|string',
            'penyampaian_jawaban' => 'nullable|in:Segera,Dalam 24 jam,Lebih dari 24 jam',
            'apoteker_penjawab' => 'nullable|string|max:255',
            'tanggal_jawab' => 'nullable|date',
            'waktu_jawab' => 'nullable',
            'metode_jawab' => 'nullable|in:Lisan,Tertulis,Telepon',
        ]);

        $validated = array_merge($validated, $this->flagsFromRequest($request), [
            'kehamilan' => $request->boolean('kehamilan'),
            'menyusui' => $request->boolean('menyusui'),
        ]);

        $pio->update($validated);

        return redirect()->route('inventory.pio.index')->with('success', 'Data PIO berhasil diperbarui.');
    }

    public function destroy(Pio $pio)
    {
        $pio->delete();

        return redirect()->route('inventory.pio.index')->with('success', 'Data PIO berhasil dihapus.');
    }

    /**
     * Nomor PIO otomatis PIOxxxx, mengikuti public/apotekberlian/masuk/modul/mod_pio/pio.php
     * tapi memperbaiki off-by-one pada posisi SUBSTRING (legacy pakai SUBSTRING(no_pio, 5)
     * yang membuang 1 digit dari nomor 4-digitnya; di sini pakai posisi 4 yang benar).
     */
    private function nextNoPio(): string
    {
        $maxNo = DB::table('pio')
            ->where('no_pio', 'like', 'PIO%')
            ->selectRaw('MAX(CAST(SUBSTRING(no_pio, 4) AS UNSIGNED)) as max_no')
            ->value('max_no');

        return 'PIO' . str_pad((int) $maxNo + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Petakan checkbox jenis_pertanyaan[] jadi 14 kolom boolean terpisah,
     * mengikuti aksi_pio.php.
     */
    private function flagsFromRequest(Request $request): array
    {
        $selected = $request->input('jenis_pertanyaan', []);
        $flags = [];

        foreach (array_keys(Pio::JENIS_PERTANYAAN) as $key) {
            $flags['jenis_pertanyaan_' . $key] = in_array($key, $selected, true);
        }

        return $flags;
    }
}
