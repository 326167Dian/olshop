<?php

namespace App\Http\Controllers;

use App\Models\BarangSupplier;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventorySupplierController extends Controller
{
    /**
     * Modul "Supplier" (module=supplier), mengikuti
     * public/apotekberlian/masuk/modul/mod_supplier/supplier.php.
     */
    public function index()
    {
        $supplier = Supplier::orderBy('id_supplier')->get();

        return view('inventory.supplier.index', [
            'judul' => 'Inventory',
            'supplier' => $supplier,
        ]);
    }

    public function create()
    {
        return view('inventory.supplier.create', [
            'judul' => 'Inventory',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nm_supplier' => 'required|string|max:100',
            'tlp_supplier' => 'nullable|string|max:30',
            'alamat_supplier' => 'nullable|string',
            'ket_supplier' => 'nullable|string',
        ]);

        if (Supplier::where('nm_supplier', $validated['nm_supplier'])
            ->where('tlp_supplier', $validated['tlp_supplier'] ?? '')
            ->exists()) {
            return back()->withInput()->with('error', 'Nama Supplier dengan nomor telepon ini sudah ada!');
        }

        Supplier::create([
            'nm_supplier' => $validated['nm_supplier'],
            'tlp_supplier' => $validated['tlp_supplier'] ?? '',
            'alamat_supplier' => $validated['alamat_supplier'] ?? '',
            'ket_supplier' => $validated['ket_supplier'] ?? '',
        ]);

        return redirect()->route('inventory.supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('inventory.supplier.edit', [
            'judul' => 'Inventory',
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'nm_supplier' => 'required|string|max:100',
            'tlp_supplier' => 'nullable|string|max:30',
            'alamat_supplier' => 'nullable|string',
            'ket_supplier' => 'nullable|string',
        ]);

        $supplier->update([
            'nm_supplier' => $validated['nm_supplier'],
            'tlp_supplier' => $validated['tlp_supplier'] ?? '',
            'alamat_supplier' => $validated['alamat_supplier'] ?? '',
            'ket_supplier' => $validated['ket_supplier'] ?? '',
        ]);

        return redirect()->route('inventory.supplier.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('inventory.supplier.index')->with('success', 'Supplier berhasil dihapus.');
    }

    /**
     * Data obat milik supplier (tabel barang_supplier), mengikuti case 'dataobat'
     * di supplier.php + tbl_detail.php. Legacy tidak punya input harga beli manual
     * di form ini (hrgsat_brgsupplier selalu default 0 lewat non-strict mode) --
     * dipertahankan sama, bukan ditambah fitur baru.
     */
    public function dataobat(Supplier $supplier)
    {
        $supplier->load('barang.barang');

        return view('inventory.supplier.dataobat', [
            'judul' => 'Inventory',
            'supplier' => $supplier,
        ]);
    }

    public function simpanBarang(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'kd_barang' => 'required|string',
        ]);

        $barang = Product::where('kd_barang', $validated['kd_barang'])->first();

        if (!$barang) {
            return back()->with('error', 'Barang tidak ditemukan.');
        }

        if (BarangSupplier::where('id_supplier', $supplier->id_supplier)->where('id_barang', $barang->id_barang)->exists()) {
            return back()->with('error', 'Barang ini sudah terdaftar untuk supplier tersebut.');
        }

        BarangSupplier::create([
            'id_supplier' => $supplier->id_supplier,
            'id_barang' => $barang->id_barang,
            'hrgsat_brgsupplier' => 0,
        ]);

        return redirect()->route('inventory.supplier.dataobat', $supplier->id_supplier)
            ->with('success', 'Data obat supplier berhasil ditambahkan.');
    }

    public function hapusBarang(BarangSupplier $barangSupplier)
    {
        $idSupplier = $barangSupplier->id_supplier;
        $barangSupplier->delete();

        return redirect()->route('inventory.supplier.dataobat', $idSupplier)
            ->with('success', 'Data obat supplier berhasil dihapus.');
    }

    /**
     * Autocomplete nama obat (AJAX), sama seperti InventorySwamedikasiController::obatSearch.
     */
    public function obatSearch(Request $request)
    {
        $query = trim((string) $request->input('query', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $items = Product::where('nm_barang', 'like', '%' . $query . '%')
            ->orderBy('nm_barang')
            ->limit(20)
            ->get(['kd_barang', 'nm_barang']);

        return response()->json($items->map(fn ($item) => [
            'nm_barang' => $item->nm_barang,
            'kd_barang' => (string) $item->kd_barang,
        ]));
    }

    /**
     * Laporan hutang supplier (transaksi trbmasuk dengan carabayar = KREDIT),
     * mengikuti case 'hutang' di supplier.php. Tidak ada model Eloquent untuk
     * trbmasuk (modul Barang Masuk belum diadaptasi) jadi dibaca langsung via DB::table.
     */
    public function hutang(Supplier $supplier)
    {
        $hutang = DB::table('trbmasuk')
            ->where('id_supplier', $supplier->id_supplier)
            ->where('carabayar', 'KREDIT')
            ->orderByDesc('tgl_trbmasuk')
            ->get();

        return view('inventory.supplier.hutang', [
            'judul' => 'Inventory',
            'supplier' => $supplier,
            'hutang' => $hutang,
            'totalHutang' => $hutang->sum('ttl_trbmasuk'),
        ]);
    }

    /**
     * Cetak daftar supplier, mengikuti mod_supplier/print_supplier.php
     * (browser-print, ganti FPDF).
     */
    public function print()
    {
        return view('inventory.supplier.print', [
            'supplier' => Supplier::orderBy('id_supplier')->get(),
            'dicetakOleh' => Auth::guard('admin')->user()->nama_lengkap,
        ]);
    }
}
