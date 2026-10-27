<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Product;
use App\Models\RiwayatPelanggan;
use App\Models\RiwayatPelangganObat;
use App\Models\Setheader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventorySwamedikasiController extends Controller
{
    /**
     * Modul "Swamedikasi" (module=swamedikasi), mengikuti
     * public/apotekberlian/masuk/modul/mod_swamedikasi/swamedikasi.php (case default).
     * Tabelnya 'riwayat_pelanggan' + 'riwayat_pelanggan_obat' (bukan tabel 'swamedikasi').
     * Tidak digerbang flag admin manapun di legacy — semua admin yang login bisa akses.
     */
    public function index()
    {
        $riwayat = RiwayatPelanggan::with(['pelanggan', 'obat'])
            ->orderByDesc('tgl')
            ->get();

        return view('inventory.swamedikasi.index', [
            'judul' => 'Inventory',
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * Halaman riwayat + form tambah per pelanggan, mengikuti case 'riwayat' di
     * swamedikasi.php (dipanggil sebagai module=pelanggan&act=riwayat di legacy).
     */
    public function riwayat(Request $request)
    {
        $idPelanggan = (int) $request->query('id_pelanggan', 0);
        $pelanggan = Pelanggan::find($idPelanggan);

        if (!$pelanggan) {
            return redirect()->route('inventory.pelanggan.index')->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $tglAwal = $request->query('tgl_awal', '');
        $tglAkhir = $request->query('tgl_akhir', now()->format('Y-m-d'));

        $query = RiwayatPelanggan::with('obat')->where('id_pelanggan', $idPelanggan);
        if ($tglAwal) {
            $query->whereDate('tgl', '>=', $tglAwal);
        }
        if ($tglAkhir) {
            $query->whereDate('tgl', '<=', $tglAkhir);
        }

        return view('inventory.swamedikasi.riwayat', [
            'judul' => 'Inventory',
            'pelanggan' => $pelanggan,
            'riwayat' => $query->orderByDesc('tgl')->get(),
            'tglAwal' => $tglAwal,
            'tglAkhir' => $tglAkhir,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|integer|exists:pelanggan,id_pelanggan',
            'tgl' => 'required|date',
            'diagnosa' => 'nullable|string',
            'foto' => 'nullable|image|max:1024',
            'followup' => 'nullable|string',
            'obat_kd' => 'nullable|array',
            'obat_kd.*' => 'nullable|string',
            'aturan_pakai' => 'nullable|array',
            'aturan_pakai.*' => 'nullable|string',
        ]);

        $obatItems = $this->resolveObatItems($request->input('obat_kd', []), $request->input('aturan_pakai', []));

        $fotoPath = $request->hasFile('foto') ? $request->file('foto')->store('riwayat_pelanggan', 'public') : '';

        DB::transaction(function () use ($validated, $obatItems, $fotoPath) {
            $riwayat = RiwayatPelanggan::create([
                'id_pelanggan' => $validated['id_pelanggan'],
                'id_admin' => Auth::guard('admin')->user()->id_admin,
                'tgl' => $validated['tgl'],
                'diagnosa' => $validated['diagnosa'] ?? '',
                'tindakan' => $this->summarizeObat($obatItems),
                'followup' => $validated['followup'] ?? '',
                'foto' => $fotoPath,
                'foto2' => '',
                'followup_by' => '',
            ]);

            $this->saveObatItems($riwayat->id, $obatItems);
        });

        return redirect()->route('inventory.swamedikasi.riwayat', ['id_pelanggan' => $validated['id_pelanggan']])
            ->with('success', 'Riwayat berhasil disimpan.');
    }

    public function edit(RiwayatPelanggan $riwayat)
    {
        $riwayat->load('obat');

        return view('inventory.swamedikasi.edit', [
            'judul' => 'Inventory',
            'riwayat' => $riwayat,
        ]);
    }

    public function update(Request $request, RiwayatPelanggan $riwayat)
    {
        $validated = $request->validate([
            'tgl' => 'required|date',
            'diagnosa' => 'nullable|string',
            'foto' => 'nullable|image|max:1024',
            'hapus_foto' => 'nullable|boolean',
            'followup' => 'nullable|string',
            'obat_kd' => 'nullable|array',
            'obat_kd.*' => 'nullable|string',
            'aturan_pakai' => 'nullable|array',
            'aturan_pakai.*' => 'nullable|string',
        ]);

        $obatItems = $this->resolveObatItems($request->input('obat_kd', []), $request->input('aturan_pakai', []));

        $riwayatData = [
            'tgl' => $validated['tgl'],
            'diagnosa' => $validated['diagnosa'] ?? '',
            'tindakan' => $this->summarizeObat($obatItems),
            'followup' => $validated['followup'] ?? '',
        ];

        if ($request->hasFile('foto')) {
            if ($riwayat->foto) {
                Storage::disk('public')->delete($riwayat->foto);
            }
            $riwayatData['foto'] = $request->file('foto')->store('riwayat_pelanggan', 'public');
        } elseif ($request->boolean('hapus_foto')) {
            if ($riwayat->foto) {
                Storage::disk('public')->delete($riwayat->foto);
            }
            $riwayatData['foto'] = '';
        }

        DB::transaction(function () use ($riwayatData, $obatItems, $riwayat) {
            $riwayat->update($riwayatData);

            RiwayatPelangganObat::where('id_riwayat', $riwayat->id)->delete();
            $this->saveObatItems($riwayat->id, $obatItems);
        });

        return redirect()->route('inventory.swamedikasi.riwayat', ['id_pelanggan' => $riwayat->id_pelanggan])
            ->with('success', 'Riwayat berhasil diperbarui.');
    }

    public function destroy(RiwayatPelanggan $riwayat)
    {
        $idPelanggan = $riwayat->id_pelanggan;

        DB::transaction(function () use ($riwayat) {
            RiwayatPelangganObat::where('id_riwayat', $riwayat->id)->delete();
            $riwayat->delete();
        });

        return redirect()->route('inventory.swamedikasi.riwayat', ['id_pelanggan' => $idPelanggan])
            ->with('success', 'Riwayat berhasil dihapus.');
    }

    /**
     * Tandai follow up (AJAX), mengikuti mod_swamedikasi/updateFollowUp.php.
     * Foto opsional (maks 1MB), disimpan ke kolom 'foto2'.
     */
    public function followup(Request $request, RiwayatPelanggan $riwayat)
    {
        $validated = $request->validate([
            'foto2' => 'nullable|image|max:1024',
        ]);

        $admin = Auth::guard('admin')->user();

        $data = [
            'tgl_followup' => now(),
            'followup_by' => $admin->nama_lengkap ?: 'System',
        ];

        if ($request->hasFile('foto2')) {
            $data['foto2'] = $request->file('foto2')->store('riwayat_pelanggan', 'public');
        }

        $riwayat->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Follow up berhasil disimpan.',
            'tgl_followup' => $riwayat->tgl_followup->format('d-m-Y H:i'),
            'followup_by' => $riwayat->followup_by,
            'foto2_url' => $riwayat->foto2 ? asset('storage/' . $riwayat->foto2) : null,
        ]);
    }

    /**
     * Cetak seluruh riwayat satu pelanggan, mengikuti
     * mod_swamedikasi/cetak_riwayat_pdf.php (browser-print, ganti FPDF).
     */
    public function exportPdf(Request $request)
    {
        $idPelanggan = (int) $request->query('id_pelanggan', 0);
        $pelanggan = Pelanggan::findOrFail($idPelanggan);

        $riwayat = RiwayatPelanggan::with('obat')
            ->where('id_pelanggan', $idPelanggan)
            ->orderByDesc('tgl')
            ->get();

        return view('inventory.swamedikasi.export', [
            'pelanggan' => $pelanggan,
            'riwayat' => $riwayat,
            'setheader' => Setheader::first(),
        ]);
    }

    /**
     * Autocomplete nama obat (AJAX), mengikuti mod_swamedikasi/autonamabarang.php.
     */
    public function obatSearch(Request $request)
    {
        $query = trim((string) $request->input('query', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $items = Product::where('nm_barang', 'like', '%' . $query . '%')
            ->orderBy('nm_barang')
            ->limit(20)
            ->get(['kd_barang', 'nm_barang']);

        return response()->json($items->map(fn ($item) => [
            'nm_barang' => $item->nm_barang,
            'kd_barang' => (string) $item->kd_barang,
        ]));
    }

    /**
     * Cocokkan kd_barang[] + aturan_pakai[] ke data barang riil, mengikuti
     * build_riwayat_obat_items() di aksi_pelanggan.php. Baris tanpa kd_barang
     * valid (obat tidak dipilih dari hasil pencarian) diabaikan.
     */
    private function resolveObatItems(array $obatKds, array $aturanPakaiList): array
    {
        $items = [];

        foreach ($obatKds as $idx => $kdRaw) {
            $kd = trim((string) $kdRaw);
            $aturan = trim((string) ($aturanPakaiList[$idx] ?? ''));

            if ($kd === '') {
                continue;
            }

            $barang = Product::where('kd_barang', $kd)->first();
            if (!$barang) {
                continue;
            }

            $items[] = [
                'kd_barang' => $barang->kd_barang,
                'nm_barang' => $barang->nm_barang,
                'aturan_pakai' => $aturan,
            ];
        }

        return $items;
    }

    private function summarizeObat(array $obatItems): string
    {
        if (count($obatItems) < 1) {
            return 'Tanpa Obat';
        }

        $lines = array_map(function ($item) {
            $line = $item['nm_barang'] . ' (' . $item['kd_barang'] . ')';
            if ($item['aturan_pakai'] !== '') {
                $line .= ' - ' . $item['aturan_pakai'];
            }

            return $line;
        }, $obatItems);

        return implode('; ', $lines);
    }

    private function saveObatItems(int $idRiwayat, array $obatItems): void
    {
        foreach ($obatItems as $item) {
            RiwayatPelangganObat::create([
                'id_riwayat' => $idRiwayat,
                'kd_barang' => $item['kd_barang'],
                'nm_barang' => $item['nm_barang'],
                'aturan_pakai' => $item['aturan_pakai'],
            ]);
        }
    }
}
