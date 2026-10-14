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
     * Modul "Pengaturan Fitur" -- toggle tier starter/premium (tabel fitur).
     * Khusus admin level 'pemilik' DENGAN flag vendor admin.mysifa='Y' (lihat
     * Admin::isMySifa()), bukan flag biasa di Admin::PERMISSION_GROUPS supaya
     * pemilik apotek tidak bisa membuka kuncinya sendiri lewat form centang
     * izin Admin yang sudah ada.
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
     * Toggle acc='Y' untuk satu baris (starter/premium), otomatis set baris
     * lainnya ='N' -- starter dan premium eksklusif (hanya satu tier aktif).
     */
    public function update(Fitur $fitur)
    {
        $this->authorizePemilikMysifa();

        DB::transaction(function () use ($fitur) {
            Fitur::where('id_fitur', '!=', $fitur->id_fitur)->update(['acc' => 'N']);
            $fitur->update(['acc' => 'Y']);
        });

        return redirect()->route('inventory.fitur.index')
            ->with('success', "Fitur '{$fitur->fitur}' diaktifkan.");
    }

    private function authorizePemilikMysifa(): void
    {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        abort_unless($admin && $admin->isPemilik() && $admin->isMySifa(), 403, 'Anda tidak berhak mengakses halaman ini.');
    }
}
