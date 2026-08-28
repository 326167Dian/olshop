<?php

namespace App\Http\Controllers;

use App\Models\Meso;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryMesoController extends Controller
{
    /**
     * Modul "MESO" (Monitoring Efek Samping Obat), mengikuti
     * public/apotekberlian/masuk/modul/mod_meso/meso.php.
     * Tidak digerbang flag admin manapun di legacy — semua admin yang login bisa akses.
     */
    public function index()
    {
        $meso = Meso::with('pelanggan')->orderByDesc('id_meso')->get();

        return view('inventory.meso.index', [
            'judul' => 'Inventory',
            'meso' => $meso,
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

        return view('inventory.meso.create', [
            'judul' => 'Inventory',
            'pelanggan' => $pelanggan,
            'idPelanggan' => $idPelanggan,
            'umur' => $umur !== '' ? $umur . ' tahun' : '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|integer|exists:pelanggan,id_pelanggan',
            'kode_sumber_data' => 'nullable|string|max:50',
            'nama_singkat' => 'nullable|string|max:100',
            'umur' => 'nullable|string|max:20',
            'suku' => 'nullable|string|max:50',
            'berat_badan' => 'nullable|string|max:20',
            'pekerjaan' => 'nullable|string|max:100',
            'jenis_kelamin' => 'nullable|in:L,P',
            'status_hamil' => 'nullable|in:hamil,tidak_hamil,tidak_tahu',
            'penyakit_utama' => 'required|string',
            'kondisi_medis_lain_ket' => 'nullable|string|max:255',
            'kesudahan_penyakit' => 'nullable|in:sembuh,sembuh_gejala_sisa,belum_sembuh,meninggal,tidak_tahu',
            'manifestasi_eso' => 'required|string',
            'masalah_mutu_produk' => 'nullable|string',
            'tanggal_mula_eso' => 'required|date',
            'kesudahan_eso' => 'nullable|in:sembuh,sembuh_gejala_sisa,belum_sembuh,meninggal,tidak_tahu',
            'riwayat_eso' => 'nullable|string',
            'keterangan_tambahan' => 'nullable|string',
            'data_laboratorium' => 'nullable|string',
            'tanggal_pemeriksaan_lab' => 'nullable|date',
            'tanggal_laporan' => 'required|date',
            'nama_pelapor' => 'required|string|max:100',
        ]);

        $validated['gangguan_ginjal'] = $request->boolean('gangguan_ginjal');
        $validated['gangguan_hati'] = $request->boolean('gangguan_hati');
        $validated['alergi'] = $request->boolean('alergi');
        $validated['kondisi_medis_lain'] = $request->boolean('kondisi_medis_lain');
        $validated['data_obat'] = $this->obatFromRequest($request);
        $validated['created_by'] = Auth::guard('admin')->user()->nama_lengkap;

        Meso::create($validated);

        return redirect()->route('inventory.meso.index')->with('success', 'Data MESO berhasil disimpan.');
    }

    public function show(Meso $meso)
    {
        $meso->load('pelanggan');

        return view('inventory.meso.show', [
            'meso' => $meso,
        ]);
    }

    public function edit(Meso $meso)
    {
        return view('inventory.meso.edit', [
            'judul' => 'Inventory',
            'meso' => $meso,
        ]);
    }

    public function update(Request $request, Meso $meso)
    {
        $validated = $request->validate([
            'kode_sumber_data' => 'nullable|string|max:50',
            'jenis_kelamin' => 'nullable|in:L,P',
            'status_hamil' => 'nullable|in:hamil,tidak_hamil,tidak_tahu',
            'suku' => 'nullable|string|max:50',
            'berat_badan' => 'nullable|string|max:20',
            'pekerjaan' => 'nullable|string|max:100',
            'penyakit_utama' => 'required|string',
            'kondisi_medis_lain_ket' => 'nullable|string|max:255',
            'kesudahan_penyakit' => 'nullable|in:sembuh,sembuh_gejala_sisa,belum_sembuh,meninggal,tidak_tahu',
            'manifestasi_eso' => 'required|string',
            'masalah_mutu_produk' => 'nullable|string',
            'tanggal_mula_eso' => 'required|date',
            'kesudahan_eso' => 'nullable|in:sembuh,sembuh_gejala_sisa,belum_sembuh,meninggal,tidak_tahu',
            'riwayat_eso' => 'nullable|string',
            'keterangan_tambahan' => 'nullable|string',
            'data_laboratorium' => 'nullable|string',
            'tanggal_pemeriksaan_lab' => 'nullable|date',
            'tanggal_laporan' => 'required|date',
            'nama_pelapor' => 'required|string|max:100',
        ]);

        $validated['gangguan_ginjal'] = $request->boolean('gangguan_ginjal');
        $validated['gangguan_hati'] = $request->boolean('gangguan_hati');
        $validated['alergi'] = $request->boolean('alergi');
        $validated['kondisi_medis_lain'] = $request->boolean('kondisi_medis_lain');
        $validated['data_obat'] = $this->obatFromRequest($request);

        $meso->update($validated);

        return redirect()->route('inventory.meso.index')->with('success', 'Data MESO berhasil diperbarui.');
    }

    public function destroy(Meso $meso)
    {
        $meso->delete();

        return redirect()->route('inventory.meso.index')->with('success', 'Data MESO berhasil dihapus.');
    }

    /**
     * Susun array data_obat dari input obat_nama[]/obat_bentuk[]/dst, mengikuti
     * logika aksi_meso.php: baris obat cuma disimpan kalau obat_nama-nya diisi.
     */
    private function obatFromRequest(Request $request): array
    {
        $nama = $request->input('obat_nama', []);
        $bentuk = $request->input('obat_bentuk', []);
        $batch = $request->input('obat_batch', []);
        $cara = $request->input('obat_cara', []);
        $dosis = $request->input('obat_dosis', []);
        $indikasi = $request->input('obat_indikasi', []);
        $tglMula = $request->input('obat_tgl_mula', []);
        $tglAkhir = $request->input('obat_tgl_akhir', []);
        $jkn = $request->input('obat_jkn', []);
        $dicurigai = $request->input('obat_dicurigai', []);

        $dataObat = [];
        foreach ($nama as $i => $namaObat) {
            if (trim((string) $namaObat) === '') {
                continue;
            }

            $dataObat[] = [
                'nama' => $namaObat,
                'bentuk' => $bentuk[$i] ?? '',
                'batch' => $batch[$i] ?? '',
                'cara' => $cara[$i] ?? '',
                'dosis' => $dosis[$i] ?? '',
                'indikasi' => $indikasi[$i] ?? '',
                'tgl_mula' => $tglMula[$i] ?? '',
                'tgl_akhir' => $tglAkhir[$i] ?? '',
                'jkn' => isset($jkn[$i]) ? 1 : 0,
                'dicurigai' => isset($dicurigai[$i]) ? 1 : 0,
            ];
        }

        return $dataObat;
    }
}
