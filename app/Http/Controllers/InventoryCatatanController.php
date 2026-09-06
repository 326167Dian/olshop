<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Catatan;
use App\Models\WaktuKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryCatatanController extends Controller
{
    /**
     * Modul "Catatan" (grup Transaksi, flag admin `catatan`), mengikuti
     * `mod_catatan/catatan1.php` (index/act=tambah/act=edit/act=tampil) +
     * `aksi_catatan.php` (input_catatan/update_catatan/hapus). Catatan harian bebas
     * teks per shift, ditulis petugas kasir -- bukan laporan/transaksi finansial.
     *
     * **CKEditor diganti `<textarea>` biasa** (konvensi yang sama dipakai di seluruh
     * port ini, mis. Item Barang/Ujian) -- tapi baris LAMA di tabel `catatan` beneran
     * berisi HTML asli dari CKEditor (mis. `<p>ada pasien cari mixagrip</p>`), jadi
     * kolom `deskripsi` tetap dirender apa adanya (`{!! !!}`), bukan di-escape ulang,
     * supaya baris lama tidak tiba-tiba menampilkan tag `<p>` mentah. Input BARU dari
     * textarea diproses sebelum disimpan (`nl2br(e(...))`) -- efeknya sama persis
     * dengan fallback `normalize_catatan_html()` legacy ketika DOMDocument tak
     * tersedia (escape lalu ubah baris-baru jadi `<br>`), hanya lebih sederhana
     * karena tidak perlu menyaring HTML dari editor WYSIWYG lagi.
     *
     * **Bug keamanan legacy diperbaiki, tidak direplikasi:** `catatan1.php`'s
     * `act=edit` MEMANG mengecek "hanya petugas yang sama atau pemilik yang boleh
     * edit" -- tapi HANYA di level tampilan form (menyembunyikan form untuk yang
     * tidak berhak). Endpoint yang benar-benar menyimpan (`aksi_catatan.php`'s
     * `update_catatan`) TIDAK PUNYA pengecekan itu sama sekali -- siapa pun yang
     * login bisa POST langsung ke situ dan mengubah catatan orang lain. Sama seperti
     * perbaikan HAPUS di modul Stok Opname (Bulanan/Harian) sebelumnya di sesi ini,
     * `update()` di sini menegakkan pengecekan yang sama di server, bukan cuma di
     * form. `act=hapus` legacy SUDAH benar mengecek ini di endpoint aksinya sendiri
     * -- diporting apa adanya ke `destroy()`.
     *
     * Aturan bisnis direplikasi apa adanya: deskripsi persis sama dengan catatan
     * yang sudah ada ditolak (`cekganda`, mencegah submit ganda tak sengaja).
     */
    public function index()
    {
        $rows = Catatan::orderByDesc('tgl')->orderByDesc('id_catatan')->get();

        $rows = $rows->map(function ($c) {
            $c->preview = $this->previewText($c->deskripsi);

            return $c;
        });

        return view('inventory.catatan.index', [
            'judul' => 'Inventory',
            'rows' => $rows,
        ]);
    }

    public function create()
    {
        $admin = Auth::guard('admin')->user();
        $waktuKerja = WaktuKerja::where('tanggal', now()->toDateString())->where('status', 'ON')->first();

        return view('inventory.catatan.create', [
            'judul' => 'Inventory',
            'petugas' => $admin->nama_lengkap,
            'shift' => $waktuKerja->shift ?? null,
            'tglHariIni' => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl' => 'required|date',
            'shift' => 'required|integer',
            'petugas' => 'required|string|max:30',
            'deskripsi' => 'required|string',
        ]);

        $deskripsi = nl2br(e($validated['deskripsi']));

        if (Catatan::where('deskripsi', $deskripsi)->exists()) {
            return back()->withInput()->withErrors(['deskripsi' => 'Catatan sudah ada!']);
        }

        Catatan::create([
            'tgl' => $validated['tgl'],
            'shift' => $validated['shift'],
            'petugas' => $validated['petugas'],
            'deskripsi' => $deskripsi,
        ]);

        return redirect()->route('inventory.catatan.index')->with('success', 'Catatan berhasil ditambahkan.');
    }

    public function edit(Catatan $catatan)
    {
        $admin = Auth::guard('admin')->user();

        if (!$this->bolehUbah($catatan, $admin)) {
            return redirect()->route('inventory.catatan.index')->with('error', 'Catatan hanya bisa diedit oleh orang yang sama!');
        }

        return view('inventory.catatan.edit', [
            'judul' => 'Inventory',
            'catatan' => $catatan,
        ]);
    }

    public function update(Request $request, Catatan $catatan)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($this->bolehUbah($catatan, $admin), 403, 'Catatan hanya bisa diedit oleh orang yang sama!');

        $validated = $request->validate([
            'deskripsi' => 'required|string',
        ]);

        $catatan->update([
            'deskripsi' => nl2br(e($validated['deskripsi'])),
        ]);

        return redirect()->route('inventory.catatan.index')->with('success', 'Catatan berhasil diubah.');
    }

    public function show(Catatan $catatan)
    {
        return view('inventory.catatan.show', [
            'judul' => 'Inventory',
            'catatan' => $catatan,
        ]);
    }

    public function destroy(Catatan $catatan)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($this->bolehUbah($catatan, $admin), 403, 'Catatan harus dihapus orang yang sama atau pemilik apotek!');

        $catatan->delete();

        return redirect()->route('inventory.catatan.index')->with('success', 'Catatan berhasil dihapus.');
    }

    private function bolehUbah(Catatan $catatan, Admin $admin): bool
    {
        return trim((string) $catatan->petugas) === trim((string) $admin->nama_lengkap) || $admin->isPemilik();
    }

    private function previewText(string $html, int $maxLength = 120): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        if ($text === '') {
            return '-';
        }

        return mb_strlen($text, 'UTF-8') > $maxLength
            ? mb_substr($text, 0, $maxLength, 'UTF-8') . '...'
            : $text;
    }
}
