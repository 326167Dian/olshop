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
    }
}
