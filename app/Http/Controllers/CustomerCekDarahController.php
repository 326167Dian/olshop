<?php

namespace App\Http\Controllers;

use App\Models\CekDarah;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Menu "History Cek Darah" (self-service) untuk pembeli e-commerce. Tabel `pelanggan`
 * (dipakai modul Cek Darah di Inventory) tidak kenal akun e-commerce sama sekali --
 * dijembatani lewat `pelanggan.email` yang dicocokkan dengan `users.email` (pembeli
 * e-commerce selalu login via akun Google, jadi email ini sudah pasti terverifikasi
 * Google, bukan diketik manual).
 *
 * - Kalau sudah ada baris `pelanggan` dengan email yang sama persis -> otomatis
 *   "tertaut", tampilkan riwayat cek darahnya.
 * - Kalau belum -> tampilkan daftar pelanggan yang BELUM tertaut (email masih kosong)
 *   supaya pembeli bisa memilih namanya sendiri dari daftar; begitu dipilih,
 *   `pelanggan.email` diisi dengan email akun yang sedang login, menautkan keduanya
 *   untuk seterusnya. Ini murni self-service (tidak ada verifikasi identitas lain di
 *   luar nama yang dipilih), jadi dijaga di store() supaya satu akun cuma bisa tertaut
 *   ke SATU baris pelanggan, dan satu baris pelanggan tidak bisa direbut kalau sudah
 *   tertaut ke email lain.
 */
class CustomerCekDarahController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $pelanggan = Pelanggan::where('email', $user->email)->first();

        if ($pelanggan) {
            $riwayat = $pelanggan->cekDarah()->orderByDesc('waktu')->get();

            return view('frontend.v_customer.cekdarah', [
                'judul' => 'History Cek Darah',
                'pelanggan' => $pelanggan,
                'riwayat' => $riwayat,
            ]);
        }

        $pelangganList = Pelanggan::whereNull('email')->orderBy('nm_pelanggan')->get();

        return view('frontend.v_customer.cekdarah', [
            'judul' => 'History Cek Darah',
            'pelanggan' => null,
            'pelangganList' => $pelangganList,
        ]);
    }

    public function link(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|integer|exists:pelanggan,id_pelanggan',
        ]);

        $user = Auth::user();

        abort_if(
            Pelanggan::where('email', $user->email)->exists(),
            422,
            'Akun Anda sudah terhubung dengan data pelanggan.'
        );

        $pelanggan = Pelanggan::findOrFail($validated['id_pelanggan']);

        abort_if(
            !empty($pelanggan->email),
            422,
            'Data pelanggan ini sudah terhubung dengan akun lain.'
        );

        $pelanggan->email = $user->email;
        $pelanggan->save();

        return redirect()->route('customer.cekdarah.index')
            ->with('success', 'Akun Anda berhasil terhubung dengan data pelanggan ' . $pelanggan->nm_pelanggan . '.');
    }

    /**
     * "Info Detail" per baris riwayat -- pakai halaman web responsive (bukan versi
     * cetak/PDF milik Inventory yang lebarnya dipatok 80mm & auto window.print()),
     * supaya enak dibaca di HP, tapi logika pewarnaan nilai abnormal & tabel acuan
     * sama persis. Dibatasi: cuma baris cek darah milik pelanggan yang tertaut ke akun
     * yang sedang login yang boleh dilihat (firstOrFail pakai id_pelanggan, bukan
     * findOrFail($id) polos) -- supaya pembeli tidak bisa melihat hasil cek darah
     * orang lain cuma dengan mengganti angka di URL.
     */
    public function detail(int $id)
    {
        $user = Auth::user();
        $pelanggan = Pelanggan::where('email', $user->email)->first();

        abort_unless($pelanggan, 403, 'Akun Anda belum terhubung dengan data pelanggan.');

        $cekdarah = CekDarah::where('id_cekdarah', $id)
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->firstOrFail();

        $cekdarah->load('pelanggan');

        return view('frontend.v_customer.cekdarah_detail', [
            'judul' => 'Detail Hasil Cek Darah',
            'cekdarah' => $cekdarah,
        ]);
    }
}
