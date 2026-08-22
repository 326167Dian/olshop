<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\JenisObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produk = Product::where('stok_barang', '>', 0)
            ->where('status', 'active')
            ->paginate(16);
        $kategori = Category::orderBy('name', 'desc')->get();

        return view('frontend.v_produk.index', compact('produk', 'kategori'));
    }

    public function indexbackend()
    {
        $index = Product::get();
        return view('backend.product.index', compact('index'));
    }

    public function search(Request $request)
    {
        $keyword = trim($request->q);
        $keywords = preg_split('/\s+/', $keyword);

        $produk = Product::where('stok_barang', '>', 0)
            ->where('status', 'active')
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $word) {
                    $query->where(function ($sub) use ($word) {
                        $sub->where('nm_barang', 'like', "%{$word}%")
                            ->orWhere('kd_barang', 'like', "%{$word}%");
                    });
                }
            })
            ->paginate(8)
            ->appends(['q' => $keyword]);

        return view('frontend.v_produk.index', compact('produk'));
    }



    public function data(Request $request)
    {
        $query = Product::select([
            'id_barang',
            'kd_barang',
            'nm_barang',
            'status',
            'category_id',
            'jenisobat',
            'hrgsat_barang',
            'hrgjual_barang2',
            'diskon',
            'image',
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('kategori', function ($row) {
                return $row->category->name ?? '-';
            })
            ->addColumn('checkbox', function ($row) {
                $checked = $row->status === 'active' ? 'checked' : '';
                return '<input type="checkbox" class="checkItem" value="' . $row->id_barang . '" ' . $checked . '>';
            })
            ->addColumn('gambar_status', function ($row) {
                if (!empty($row->image)) {
                    return '<span class="badge bg-success">Sudah diupload</span>';
                } else {
                    return '<span class="badge bg-danger">Belum upload</span>';
                }
            })
            ->addColumn('aksi', function ($row) use ($request) {
                // ambil parameter DataTables
                $start = $request->input('start', 0);
                $length = $request->input('length', 10);

                $btn = '<div class="dropdown position-relative d-inline-block">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                    Action
                </button>
                <div class="dropdown-menu center-below p-2 shadow" style="min-width: 140px;">
                    <a href="' . route('product.show', $row->id_barang) . '" class="btn btn-info btn-sm w-100 mb-1">
                        <i class="fas fa-eye"></i> Detail
                    </a>
                    <a href="' . route('product.edit', [
                    'product' => $row->id_barang,
                    'start'   => $start,
                    'length'  => $length
                ]) . '" class="btn btn-warning btn-sm w-100 mb-1">
                        <i class="far fa-edit"></i> Edit
                    </a>
                </div>
            </div>';
                return $btn;
            })
            ->rawColumns(['aksi', 'checkbox', 'gambar_status'])
            ->make(true);
    }

    public function multipleUpdateStatus(Request $request)
    {
        $allIds = $request->input('all_ids', []);
        $activeIds = $request->input('active_ids', []);

        if (empty($allIds)) {
            return response()->json(['message' => 'Tidak ada data dikirim.'], 400);
        }

        // Semua produk default jadi inactive
        Product::whereIn('id_barang', $allIds)->update(['status' => 'inactive']);

        // Produk yang dicentang jadi active
        if (!empty($activeIds)) {
            Product::whereIn('id_barang', $activeIds)->update(['status' => 'active']);
        }

        return response()->json(['message' => 'Status produk berhasil diperbarui.']);
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('backend.product.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product, Request $request)
    {
        $categories = Category::all();
        $start = $request->query('start', 0);
        $length = $request->query('length', 10); // Ambil semua kategori untuk dropdown
        return view('backend.product.update', compact('product', 'categories', 'start', 'length'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
            'gambar_produk' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // max 2MB
            'category_id' => 'required|exists:categories,id', // Validasi kategori
            'diskon' => 'nullable|numeric', // Validasi diskon
            'promosi' => 'nullable|in:terlaris,diskon,standar', // Validasi produk promosi
        ]);

        // Cek jika ada file gambar baru yang diupload
        if ($request->hasFile('gambar_produk')) {
            // Pastikan folder 'product' di disk 'public' tersedia
            if (!Storage::disk('public')->exists('product')) {
                Storage::disk('public')->makeDirectory('product');
            }

            // Hapus gambar lama jika ada
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Simpan gambar baru ke dalam folder 'product'
            $path = $request->file('gambar_produk')->store('product', 'public');
            $product->image = $path;
        }

        // Update data lainnya
        $product->status = $request->status;
        $product->category_id = $request->category_id;
        $product->diskon = $request->diskon;
        $product->promosi = $request->promosi;
        $product->updated_by = Auth::id();
        $product->save();

        $start = $request->query('start', 0);
        $length = $request->query('length', 10);

        return redirect()->route('product.index', ['start' => $start, 'length' => $length])->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product) {}

    public function detail($id)
    {
        $jenisobat = JenisObat::get();
        $produks = Product::findOrFail($id);
        $categories = Category::orderBy('name', 'desc')->get();

        return view('frontend.v_produk.detail', [
            'judul' => 'Detail Produk',
            'kategori' => $categories,
            'produks' => $produks,
            'jenisobat' => $jenisobat
        ]);
    }

    public function produkKategori($id)
    {
        // Ambil semua kategori untuk sidebar/menu
        // $jenisobat = JenisObat::all();
        $categories = Category::orderBy('name', 'desc')->get();

        // Cari kategori berdasarkan id (karena di URL yang dikirim tetap idjenis)
        // $selectedKategori = JenisObat::where('idjenis', $id)->firstOrFail();

        // Ambil produk berdasarkan label 'jenisobat' (bukan id)
        // $produk = Product::where('jenisobat', $selectedKategori->jenisobat)->paginate(8);
        $produk = Product::where('category_id', $id)->where('status', 1)->orderBy('nm_barang', 'desc')->paginate(6);

        return view('frontend.v_produk.produkkategori', [
            'judul' => $categories->find($id)->name,
            'kategori' => $categories,
            'produk' => $produk,
        ]);
    }
}
