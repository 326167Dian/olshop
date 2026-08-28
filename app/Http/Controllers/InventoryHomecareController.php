<?php

namespace App\Http\Controllers;

use App\Models\Homecare;
use App\Models\HomecareDetail;
use App\Models\Pelanggan;
use App\Models\Setheader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryHomecareController extends Controller
{
    /**
     * Modul "Home Care" (Home Pharmacy Care), mengikuti
     * public/apotekberlian/masuk/modul/mod_homecare/homecare.php.
     * Tidak digerbang flag admin manapun di legacy — semua admin yang login bisa akses.
     */
    public function index()
    {
        $homecare = Homecare::with('pelanggan')->orderByDesc('id_homecare')->get();

        return view('inventory.homecare.index', [
            'judul' => 'Inventory',
            'homecare' => $homecare,
        ]);
    }

    public function create(Request $request)
    {
        $idPelanggan = (int) $request->query('id_pelanggan', 0);
        $pelanggan = $idPelanggan > 0 ? Pelanggan::find($idPelanggan) : null;

        $umur = $pelanggan && $pelanggan->tanggal_lahir
            ? \Carbon\Carbon::parse($pelanggan->tanggal_lahir)->age . ' tahun'
            : '';

        return view('inventory.homecare.create', [
            'judul' => 'Inventory',
            'pelanggan' => $pelanggan,
            'idPelanggan' => $idPelanggan,
            'noHomecare' => $this->nextNoHomecare(),
            'umur' => $umur,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'nullable|integer|exists:pelanggan,id_pelanggan',
            'no_homecare' => 'required|string|max:20',
            'nama_pasien' => 'required|string|max:255',
            'umur' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $homecare = Homecare::create(array_merge($validated, [
                'created_by' => Auth::guard('admin')->user()->nama_lengkap,
            ]));

            $this->saveDetail($homecare->id_homecare, $request);
        });

        return redirect()->route('inventory.homecare.index')->with('success', 'Data Home Care berhasil disimpan.');
    }

    public function show(Homecare $homecare)
    {
        $homecare->load(['pelanggan', 'detail']);

        return view('inventory.homecare.show', [
            'homecare' => $homecare,
            'setheader' => Setheader::first(),
        ]);
    }

    public function edit(Homecare $homecare)
    {
        $homecare->load('detail');

        return view('inventory.homecare.edit', [
            'judul' => 'Inventory',
            'homecare' => $homecare,
        ]);
    }

    public function update(Request $request, Homecare $homecare)
    {
        $validated = $request->validate([
            'nama_pasien' => 'required|string|max:255',
            'umur' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated, $request, $homecare) {
            $homecare->update($validated);

            HomecareDetail::where('id_homecare', $homecare->id_homecare)->delete();
            $this->saveDetail($homecare->id_homecare, $request);
        });

        return redirect()->route('inventory.homecare.index')->with('success', 'Data Home Care berhasil diperbarui.');
    }

    public function destroy(Homecare $homecare)
    {
        DB::transaction(function () use ($homecare) {
            HomecareDetail::where('id_homecare', $homecare->id_homecare)->delete();
            $homecare->delete();
        });

        return redirect()->route('inventory.homecare.index')->with('success', 'Data Home Care berhasil dihapus.');
    }

    /**
     * Nomor Home Care otomatis HCxxxx, mengikuti aksi_homecare.php.
     */
    private function nextNoHomecare(): string
    {
        $last = Homecare::orderByDesc('id_homecare')->value('no_homecare');
        $lastNo = $last ? (int) substr($last, 2) : 0;

        return 'HC' . str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Simpan baris kunjungan dari tgl_kunjungan[]/catatan_apoteker[], mengikuti
     * aksi_homecare.php: baris cuma disimpan kalau salah satu field-nya diisi.
     */
    private function saveDetail(int $idHomecare, Request $request): void
    {
        $tglKunjungan = $request->input('tgl_kunjungan', []);
        $catatanApoteker = $request->input('catatan_apoteker', []);

        $urut = 1;
        foreach ($tglKunjungan as $i => $tgl) {
            $isEmpty = trim((string) $tgl) === '' && trim((string) ($catatanApoteker[$i] ?? '')) === '';

            if ($isEmpty) {
                continue;
            }

            HomecareDetail::create([
                'id_homecare' => $idHomecare,
                'no_urut' => $urut,
                'tgl_kunjungan' => $tgl,
                'catatan_apoteker' => $catatanApoteker[$i] ?? '',
            ]);

            $urut++;
        }
    }
}
