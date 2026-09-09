<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\CompanySetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\JenisObat;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Query eager di sini pernah menjalankan boot untuk SEMUA proses (termasuk
        // artisan console, mis. `php artisan migrate`) -- pada database yang benar-benar
        // kosong (belum ada tabel sama sekali, skenario "bangun ulang dari nol"), ini
        // membuat `migrate` sendiri gagal SEBELUM sempat memuat schema dump, karena
        // tabel `company_settings` belum ada. View::share/composer hanya relevan untuk
        // request web sungguhan, jadi dilewati saat runningInConsole().
        if ($this->app->runningInConsole()) {
            return;
        }

        View::share('companySetting', CompanySetting::first());
        View::share('jenisobat', JenisObat::get());
        View::share('kategori', Category::get());
        View::composer('*', function ($view) {
            $cartCount = 0;
            $cartTotal = 0;

            if (Auth::check()) {
                $customer = Auth::user(); // Asumsi relasi user ke customer
                if ($customer) {
                    $order = Order::where('user_id', $customer->id)->where('status', 'pending')->first();
                    if ($order) {
                        // Jumlah item (total quantity)
                        $cartCount = $order->orderItems()->sum('quantity');

                        // Total harga
                        $cartTotal = $order->total_harga;
                    }
                }
            }

            $view->with('cartCount', $cartCount)->with('cartTotal', $cartTotal);
        });

        View::composer('backend.layouts.app', function ($view) {
            $pendingPaymentCount = Order::where('status', 'Proses konfirmasi pembayaran')->count();
            $latestOrderId = Order::whereNotIn('status', ['Selesai', 'pending', 'Dibatalkan'])->max('id');
            $view->with('pendingPaymentCount', $pendingPaymentCount)
                ->with('latestOrderId', $latestOrderId);
        });
    }
}
