<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Keranjang untuk pengunjung yang belum login (disimpan di session), supaya
 * orang yang datang dari iklan bisa langsung menambah produk tanpa harus
 * daftar dulu. Saat login (Google), isinya dipindahkan ke keranjang database
 * lewat mergeInto().
 */
class GuestCart
{
    public const SESSION_KEY = 'guest_cart';

    /** Cache per-request (View composer '*' dipanggil untuk setiap view). */
    private static ?array $summaryCache = null;

    /** @return array<string,int> [id_barang => qty] */
    public static function items(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    public static function add(Product $produk, int $qty = 1): void
    {
        $items = self::items();
        $key = (string) $produk->id_barang;
        $items[$key] = min(($items[$key] ?? 0) + $qty, max((int) $produk->stok_barang, 0));
        session()->put(self::SESSION_KEY, $items);
        self::$summaryCache = null;
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
        self::$summaryCache = null;
    }

    public static function hargaFinal(Product $produk): float
    {
        $harga = $produk->hrgjual_barang2;
        if ($produk->diskon > 0) {
            $harga -= ($produk->hrgjual_barang2 * $produk->diskon / 100);
        }
        return $harga;
    }

    /** @return array{count:int,total:float} */
    public static function summary(): array
    {
        if (self::$summaryCache !== null) {
            return self::$summaryCache;
        }

        $items = self::items();
        if (empty($items)) {
            return self::$summaryCache = ['count' => 0, 'total' => 0];
        }

        $count = 0;
        $total = 0;
        $produk = Product::whereIn('id_barang', array_keys($items))->get();
        foreach ($produk as $p) {
            $qty = $items[(string) $p->id_barang] ?? 0;
            $count += $qty;
            $total += self::hargaFinal($p) * $qty;
        }

        return self::$summaryCache = ['count' => $count, 'total' => $total];
    }

    /**
     * Pindahkan isi keranjang tamu ke order 'pending' milik user. Mengembalikan
     * jumlah item yang berhasil dipindahkan.
     */
    public static function mergeInto(User $user): int
    {
        $items = self::items();
        if (empty($items)) {
            return 0;
        }

        $moved = 0;
        DB::transaction(function () use ($items, $user, &$moved) {
            $order = Order::firstOrCreate(['user_id' => $user->id, 'status' => 'pending']);
            $produk = Product::whereIn('id_barang', array_keys($items))->get();

            foreach ($produk as $p) {
                $qty = (int) ($items[(string) $p->id_barang] ?? 0);
                if ($qty <= 0 || $p->stok_barang <= 0) {
                    continue;
                }

                $harga = self::hargaFinal($p);
                $item = OrderItem::firstOrNew(['order_id' => $order->id, 'produk_id' => $p->id_barang]);
                $sudahAda = $item->exists ? (int) $item->quantity : 0;
                // Jangan melebihi stok yang tersedia
                $tambah = max(0, min($qty, (int) $p->stok_barang - $sudahAda));
                if ($tambah === 0) {
                    continue;
                }

                if (!$item->exists) {
                    $item->harga = $harga;
                    $item->quantity = 0;
                }
                $item->quantity = $sudahAda + $tambah;
                $item->save();

                $order->total_harga += $harga * $tambah;
                $moved += $tambah;
            }

            $order->save();
        });

        self::clear();

        return $moved;
    }
}
