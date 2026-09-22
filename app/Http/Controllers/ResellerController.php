<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CaptureResellerReferral;
use App\Models\CompanySetting;
use App\Models\Order;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class ResellerController extends Controller
{
    /**
     * Link referral pendek reseller (mis. domain/2). Cukup simpan cookie referral
     * lalu lempar ke halaman utama biasa -- tidak ada halaman/kode referral yang
     * terlihat oleh calon pelanggan.
     */
    public function referral($resellerId)
    {
        if (Reseller::whereKey($resellerId)->exists()) {
            Cookie::queue('reseller_ref', $resellerId, CaptureResellerReferral::COOKIE_MINUTES);
        }

        return redirect()->route('home-page');
    }

    /**
     * Halaman domain/reseller/register -- tiga kondisi ditangani di satu view yang
     * sama (bukan 3 route terpisah) supaya URL-nya tetap satu selama proses:
     * 1. Belum login (guard web) -> tombol "Daftar dengan Google".
     * 2. Sudah login, belum punya profil reseller -> form isi data reseller.
     * 3. Sudah login DAN sudah punya profil reseller -> lempar ke halaman reseller
     *    (tidak perlu daftar ulang).
     */
    public function register()
    {
        $user = Auth::guard('web')->user();

        if ($user && $user->reseller) {
            return redirect()->route('reseller.home');
        }

        return view('frontend.reseller.register', [
            'judul' => 'Daftar Reseller',
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('web')->user();
        abort_unless($user, 403, 'Silakan login dengan Google terlebih dahulu.');

        // Satu akun cuma boleh daftar sekali -- kalau sudah punya profil, jangan
        // dibuat ulang/ditimpa lewat form ini, cukup lempar ke halaman reseller.
        if ($user->reseller) {
            return redirect()->route('reseller.home');
        }

        // nama_bank & no_rekening sengaja opsional saat daftar -- kebanyakan calon
        // reseller enggan menulis data rekening sebelum benar-benar punya komisi.
        // Diminta lagi lewat halaman "Update Data Diri" (lihat editProfil/updateProfil)
        // begitu mereka sudah login dan siap menerima komisi.
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_hp' => 'required|string|max:30',
            'alamat' => 'required|string',
            'nama_bank' => 'nullable|string|max:255',
            'no_rekening' => 'nullable|string|max:50',
        ]);

        Reseller::create(array_merge($validated, [
            'user_id' => $user->id,
        ]));

        return redirect()->route('reseller.home')->with('success', 'Pendaftaran reseller berhasil.');
    }

    /**
     * Form untuk reseller yang sudah terdaftar melengkapi/mengubah data diri
     * mereka sendiri, terutama nama bank & no rekening yang tadinya dikosongkan
     * saat daftar.
     */
    public function editProfil()
    {
        $user = Auth::guard('web')->user();

        if (!$user || !$user->reseller) {
            return redirect()->route('reseller.register');
        }

        return view('frontend.reseller.edit-profil', [
            'judul' => 'Update Data Diri',
            'reseller' => $user->reseller,
        ]);
    }

    public function updateProfil(Request $request)
    {
        $user = Auth::guard('web')->user();

        if (!$user || !$user->reseller) {
            return redirect()->route('reseller.register');
        }

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_hp' => 'required|string|max:30',
            'alamat' => 'required|string',
            'nama_bank' => 'nullable|string|max:255',
            'no_rekening' => 'nullable|string|max:50',
        ]);

        $user->reseller->update($validated);

        return redirect()->route('reseller.home')->with('success', 'Data diri berhasil diperbarui.');
    }

    /**
     * Halaman utama reseller: daftar pelanggan yang mereka rekrut + omzet bulan
     * berjalan. Hanya bisa diakses reseller yang sudah benar-benar terdaftar.
     */
    public function home()
    {
        $user = Auth::guard('web')->user();

        if (!$user || !$user->reseller) {
            return redirect()->route('reseller.register');
        }

        $reseller = $user->reseller;
        $komisiPersen = CompanySetting::first()->komisi_reseller ?? 5;

        // Daftar pelanggan yang direkrut reseller ini, beserta ringkasan belanja
        // (all-time) tiap pelanggan -- hanya transaksi yang sudah "Selesai" yang
        // dihitung, sama seperti syarat sinkron ke trkasir.
        $pelanggan = User::where('referred_by_reseller_id', $reseller->id)
            ->withCount(['orders as total_pesanan' => function ($q) {
                $q->where('status', 'Selesai');
            }])
            ->withSum(['orders as total_belanja' => function ($q) {
                $q->where('status', 'Selesai');
            }], 'total_harga')
            ->orderByDesc('created_at')
            ->get();

        $pelangganIds = $pelanggan->pluck('id');

        $omzetBulanIni = Order::whereIn('user_id', $pelangganIds)
            ->where('status', 'Selesai')
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_harga');

        $komisiBulanIni = round($omzetBulanIni * $komisiPersen / 100, 2);

        return view('frontend.reseller.home', [
            'judul' => 'Reseller',
            'reseller' => $reseller,
            'pelanggan' => $pelanggan,
            'omzetBulanIni' => $omzetBulanIni,
            'komisiBulanIni' => $komisiBulanIni,
            'komisiPersen' => $komisiPersen,
        ]);
    }

    /**
     * Detail belanja satu pelanggan hasil rekrutan reseller yang sedang login:
     * barang apa saja yang dibeli beserta komisinya. firstOrFail() di bawah
     * sekaligus jadi otorisasi -- reseller lain tidak bisa lihat pelanggan yang
     * bukan rekrutannya walau tahu/menebak user id di URL.
     */
    public function pelangganDetail($userId)
    {
        $me = Auth::guard('web')->user();

        if (!$me || !$me->reseller) {
            return redirect()->route('reseller.register');
        }

        $pelanggan = User::where('id', $userId)
            ->where('referred_by_reseller_id', $me->reseller->id)
            ->firstOrFail();

        $komisiPersen = CompanySetting::first()->komisi_reseller ?? 5;

        $orders = Order::where('user_id', $pelanggan->id)
            ->where('status', 'Selesai')
            ->with('orderItems.produk')
            ->orderByDesc('updated_at')
            ->get();

        return view('frontend.reseller.pelanggan-detail', [
            'judul' => 'Detail Pelanggan',
            'reseller' => $me->reseller,
            'pelanggan' => $pelanggan,
            'orders' => $orders,
            'komisiPersen' => $komisiPersen,
        ]);
    }
}
