<?php

namespace App\Http\Controllers;

use App\Models\Cpp;
use App\Models\CppDetail;
use App\Models\Pelanggan;
use App\Models\Setheader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryCppController extends Controller
{
    /**
     * Modul "CPP" (Catatan Pengobatan Pasien), mengikuti
     * public/apotekberlian/masuk/modul/mod_cpp/cpp.php.
     * Tidak digerbang flag admin manapun di legacy — semua admin yang login bisa akses.
     */
    public function index()
    {
        $cpp = Cpp::with('pelanggan')->orderByDesc('id_cpp')->get();

        return view('inventory.cpp.index', [
            'judul' => 'Inventory',
            'cpp' => $cpp,
        ]);
    }

    public function create(Request $request)
    {
        $idPelanggan = (int) $request->query('id_pelanggan', 0);
        $pelanggan = $idPelanggan > 0 ? Pelanggan::find($idPelanggan) : null;

        $jk = '';
        $umur = '';
        if ($pelanggan) {
            $jk = $pelanggan->jenis_kelamin === 'PRIA' ? 'Laki-laki' : ($pelanggan->jenis_kelamin === 'WANITA' ? 'Perempuan' : '');
            $umur = $pelanggan->tanggal_lahir ? \Carbon\Carbon::parse($pelanggan->tanggal_lahir)->age . ' tahun' : '';
        }

        $setheader = Setheader::first();

        return view('inventory.cpp.create', [
            'judul' => 'Inventory',
            'pelanggan' => $pelanggan,
            'idPelanggan' => $idPelanggan,
            'noCpp' => $this->nextNoCpp(),
            'jk' => $jk,
            'umur' => $umur,
            'namaApoteker' => $setheader->empat ?? '',
            'sipaApoteker' => $setheader->tujuh ?? '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'nullable|integer|exists:pelanggan,id_pelanggan',
            'no_cpp' => 'required|string|max:20',
            'nama_pasien' => 'required|string|max:255',
            'jk' => 'required|in:Laki-laki,Perempuan',
            'umur' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'tgl_ttd' => 'nullable|string|max:100',
            'thn_ttd' => 'nullable|string|max:10',
            'nama_apoteker' => 'nullable|string|max:255',
            'sipa_apoteker' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $cpp = Cpp::create(array_merge($validated, [
                'created_by' => Auth::guard('admin')->user()->nama_lengkap,
            ]));

            $this->saveDetail($cpp->id_cpp, $request);
        });

        return redirect()->route('inventory.cpp.index')->with('success', 'Data CPP berhasil disimpan.');
    }

    public function show(Cpp $cpp)
    {
        $cpp->load(['pelanggan', 'detail']);

        return view('inventory.cpp.show', [
            'cpp' => $cpp,
            'setheader' => Setheader::first(),
        ]);
    }

    public function edit(Cpp $cpp)
    {
        $cpp->load('detail');

        return view('inventory.cpp.edit', [
            'judul' => 'Inventory',
            'cpp' => $cpp,
        ]);
    }

    public function update(Request $request, Cpp $cpp)
    {
        $validated = $request->validate([
            'nama_pasien' => 'required|string|max:255',
            'jk' => 'required|in:Laki-laki,Perempuan',
            'umur' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'tgl_ttd' => 'nullable|string|max:100',
            'thn_ttd' => 'nullable|string|max:10',
            'nama_apoteker' => 'nullable|string|max:255',
            'sipa_apoteker' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated, $request, $cpp) {
            $cpp->update($validated);

            CppDetail::where('id_cpp', $cpp->id_cpp)->delete();
            $this->saveDetail($cpp->id_cpp, $request);
        });

        return redirect()->route('inventory.cpp.index')->with('success', 'Data CPP berhasil diperbarui.');
    }

    public function destroy(Cpp $cpp)
    {
        // cpp_detail dihapus manual (tanpa FK cascade di Eloquent), meniru
        // "delete will cascade" di legacy yang mengandalkan FK constraint di DB.
        DB::transaction(function () use ($cpp) {
            CppDetail::where('id_cpp', $cpp->id_cpp)->delete();
            $cpp->delete();
        });

        return redirect()->route('inventory.cpp.index')->with('success', 'Data CPP berhasil dihapus.');
    }

    /**
     * Nomor CPP otomatis CPPxxxx, mengikuti aksi/cpp.php.
     */
    private function nextNoCpp(): string
    {
        $last = Cpp::orderByDesc('id_cpp')->value('no_cpp');
        $lastNo = $last ? (int) substr($last, 3) : 0;

        return 'CPP' . str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Simpan baris detail obat dari tanggal[]/nama_dokter[]/nama_obat_dosis[]/catatan[],
     * mengikuti aksi_cpp.php: baris cuma disimpan kalau salah satu field-nya diisi.
     */
    private function saveDetail(int $idCpp, Request $request): void
    {
        $tanggal = $request->input('tanggal', []);
        $namaDokter = $request->input('nama_dokter', []);
        $namaObatDosis = $request->input('nama_obat_dosis', []);
        $catatan = $request->input('catatan', []);

        $urut = 1;
        foreach ($tanggal as $i => $tgl) {
            $isEmpty = trim((string) $tgl) === ''
                && trim((string) ($namaDokter[$i] ?? '')) === ''
                && trim((string) ($namaObatDosis[$i] ?? '')) === ''
                && trim((string) ($catatan[$i] ?? '')) === '';

            if ($isEmpty) {
                continue;
            }

            CppDetail::create([
                'id_cpp' => $idCpp,
                'no_urut' => $urut,
                'tanggal' => $tgl,
                'nama_dokter' => $namaDokter[$i] ?? '',
                'nama_obat_dosis' => $namaObatDosis[$i] ?? '',
                'catatan' => $catatan[$i] ?? '',
            ]);

            $urut++;
        }
    }
}
