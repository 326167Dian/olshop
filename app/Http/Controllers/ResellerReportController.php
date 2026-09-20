<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Order;
use App\Models\Reseller;
use App\Models\User;

/**
 * Laporan reseller untuk admin -- read-only dengan sengaja (tidak ada
 * store/update/destroy). Admin cuma boleh melihat data reseller, pelanggan yang
 * mereka rekrut, dan rincian transaksi untuk keperluan hitung/bayar komisi.
 */
class ResellerReportController extends Controller
{
    public function index()
    {
        $komisiPersen = CompanySetting::first()->komisi_reseller ?? 5;

        $resellers = Reseller::withCount('referredCustomers')->orderBy('nama_lengkap')->get();

        foreach ($resellers as $reseller) {
            $pelangganIds = $reseller->referredCustomers()->pluck('id');

            $reseller->omzet = Order::whereIn('user_id', $pelangganIds)
                ->where('status', 'Selesai')
                ->sum('total_harga');

            $reseller->komisi = round($reseller->omzet * $komisiPersen / 100, 2);
        }

        return view('backend.reseller-report.index', [
            'judul' => 'Reseller',
            'resellers' => $resellers,
            'komisiPersen' => $komisiPersen,
        ]);
    }

    public function show(Reseller $reseller)
    {
        $komisiPersen = CompanySetting::first()->komisi_reseller ?? 5;

        $pelanggan = User::where('referred_by_reseller_id', $reseller->id)
            ->withCount(['orders as total_pesanan' => function ($q) {
                $q->where('status', 'Selesai');
            }])
            ->withSum(['orders as total_belanja' => function ($q) {
                $q->where('status', 'Selesai');
            }], 'total_harga')
            ->orderByDesc('created_at')
            ->get();

        $omzet = $pelanggan->sum('total_belanja');
        $komisi = round($omzet * $komisiPersen / 100, 2);

        return view('backend.reseller-report.show', [
            'judul' => 'Detail Reseller',
            'reseller' => $reseller,
            'pelanggan' => $pelanggan,
            'komisiPersen' => $komisiPersen,
            'omzet' => $omzet,
            'komisi' => $komisi,
        ]);
    }

    public function pelangganDetail(Reseller $reseller, $userId)
    {
        $pelanggan = User::where('id', $userId)
            ->where('referred_by_reseller_id', $reseller->id)
            ->firstOrFail();

        $komisiPersen = CompanySetting::first()->komisi_reseller ?? 5;

        $orders = Order::where('user_id', $pelanggan->id)
            ->where('status', 'Selesai')
            ->with('orderItems.produk')
            ->orderByDesc('updated_at')
            ->get();

        return view('backend.reseller-report.pelanggan-detail', [
            'judul' => 'Detail Transaksi Pelanggan',
            'reseller' => $reseller,
            'pelanggan' => $pelanggan,
            'orders' => $orders,
            'komisiPersen' => $komisiPersen,
        ]);
    }
}
