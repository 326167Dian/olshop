<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Fitur;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryFiturController extends Controller
{
    /**
     * Baris tier starter/premium bersifat eksklusif (hanya satu yang aktif),
     * beda dengan baris fitur lain seperti Fitur::NAMA_ECOMMERCE yang
     * independen (bisa Aktif/Nonaktif tanpa memengaruhi baris lain).
     */
    private const TIER_EKSKLUSIF = ['starter', 'premium'];

    /**
     * Modul "Pengaturan Fitur" -- toggle tier starter/premium & fitur
     * independen seperti E-Commerce (tabel fitur). Khusus admin level
     * 'pemilik' DENGAN flag vendor admin.mysifa='Y' (lihat Admin::isMySifa()),
     * bukan flag biasa di Admin::PERMISSION_GROUPS supaya pemilik apotek tidak
     * bisa membuka kuncinya sendiri lewat form centang izin Admin yang sudah
     * ada.
     */
    public function index()
    {
        $this->authorizePemilikMysifa();

        return view('inventory.fitur.index', [
            'judul' => 'Inventory',
            'fiturList' => Fitur::orderBy('id_fitur')->get(),
            'jumlahStok' => Product::where('stok_barang', '>', 0)->count(),
            'batasStarter' => Fitur::BATAS_STOK_STARTER,
        ]);
    }

    /**
     * Tier starter/premium: toggle acc='Y' untuk baris ini, otomatis set
     * baris tier lainnya ='N' (eksklusif, hanya satu tier aktif). Fitur
     * independen (mis. E-Commerce): toggle langsung Y<->N, tidak memengaruhi
     * baris lain.
     */
    public function update(Fitur $fitur)
    {
        $this->authorizePemilikMysifa();

        if (in_array($fitur->fitur, self::TIER_EKSKLUSIF, true)) {
            DB::transaction(function () use ($fitur) {
                Fitur::whereIn('fitur', self::TIER_EKSKLUSIF)
                    ->where('id_fitur', '!=', $fitur->id_fitur)
                    ->update(['acc' => 'N']);
                $fitur->update(['acc' => 'Y']);
            });

            return redirect()->route('inventory.fitur.index')
                ->with('success', "Fitur '{$fitur->fitur}' diaktifkan.");
        }

        $sedangAktif = strtoupper($fitur->acc) === 'Y';
        $fitur->update(['acc' => $sedangAktif ? 'N' : 'Y']);

        return redirect()->route('inventory.fitur.index')
            ->with('success', $sedangAktif
                ? "Fitur '{$fitur->fitur}' dinonaktifkan."
                : "Fitur '{$fitur->fitur}' diaktifkan.");
    }

    private function authorizePemilikMysifa(): void
    {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        abort_unless($admin && $admin->isPemilik() && $admin->isMySifa(), 403, 'Anda tidak berhak mengakses halaman ini.');
    }
}
