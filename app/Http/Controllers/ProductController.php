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

    /**
     * Barang stok > 0 yang gambarnya belum lengkap -- baik kolom `image` masih
     * kosong, MAUPUN sudah terisi tapi filenya sendiri tidak ada di disk `public`
     * (link putus, file terhapus/tidak pernah ter-upload penuh). Dipakai untuk
     * halaman "Gambar Tidak Lengkap" (tautan terpisah dari Data Produk, bukan
     * filter di halaman yang sama).
     */
    public function missingImage()
    {
        $ids = $this->missingImageIds();

        return view('backend.product.missing-image', [
            'totalCount' => count($ids),
        ]);
    }

    /**
     * Upload gambar inline dari halaman "Gambar Tidak Lengkap" (AJAX, satu field
     * saja) -- terpisah dari update() yang mewajibkan status/kategori/dll sekaligus
     * lewat redirect halaman penuh, tidak cocok dipakai untuk edit-di-tempat.
     */
    public function updateImage(Request $request, Product $product)
    {
        $request->validate([
            'gambar_produk' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (!Storage::disk('public')->exists('product')) {
            Storage::disk('public')->makeDirectory('product');
        }

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->image = $request->file('gambar_produk')->store('product', 'public');
        $product->updated_by = Auth::id();
        $product->save();

        return response()->json([
            'message' => 'Gambar produk berhasil diunggah.',
            'image_url' => asset('storage/' . $product->image),
        ]);
    }

    /**
     * Edit inline Deskripsi Obat (ket_barang) dari halaman Data Produk (AJAX,
     * dipanggil dari modal + CKEditor) -- satu field saja, mengikuti pola
     * InventoryBarangController::updateIndikasi()/updateJenisobat() untuk edit
     * inline di tabel.
     */
    public function updateKetBarangInline(Request $request, Product $product)
    {
        $product->ket_barang = (string) $request->input('ket_barang', '');
        $product->updated_by = Auth::id();
        $product->save();

        return response()->json(['message' => 'Deskripsi obat berhasil diperbarui.']);
    }

    public function missingImageData()
    {
        $ids = $this->missingImageIds();

        $query = Product::whereIn('id_barang', $ids)->select([
            'id_barang', 'kd_barang', 'nm_barang', 'stok_barang', 'hrgjual_barang2', 'image',
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('gambar_status', function ($row) {
                if (empty($row->image)) {
                    return '<span class="badge bg-danger">Belum upload</span>';
                }

                return '<span class="badge bg-warning text-dark">File tidak ditemukan</span>';
            })
            ->editColumn('hrgjual_barang2', fn ($row) => number_format($row->hrgjual_barang2, 0, ',', '.'))
            ->addColumn('aksi', function ($row) {
                return '<input type="file" class="form-control form-control-sm input-upload-gambar" '
                    . 'data-id="' . $row->id_barang . '" accept="image/png,image/jpeg,image/jpg">';
            })
            ->rawColumns(['gambar_status', 'aksi'])
            ->make(true);
    }

    /**
     * @return int[] id_barang produk stok>0 dengan gambar kosong atau file hilang
     *               dari disk. Pengecekan file per baris ini murni I/O filesystem
     *               lokal (bukan query DB tambahan per baris), sekali jalan untuk
     *               seluruh produk stok>0 (skala ratusan, bukan ribuan) -- bukan
     *               kelas N+1 query yang perlu dihindari.
     */
    private function missingImageIds(): array
    {
        $produk = Product::where('stok_barang', '>', 0)->get(['id_barang', 'image']);

        $ids = [];
        foreach ($produk as $p) {
            if (empty($p->image) || !Storage::disk('public')->exists($p->image)) {
                $ids[] = $p->id_barang;
            }
        }

        return $ids;
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
            'stok_barang',
            'hrgjual_barang2',
            'diskon',
            'image',
            'ket_barang',
        ]);

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('kategori', function ($row) use ($categories) {
                $options = '<option value="">- Pilih Kategori -</option>';
                foreach ($categories as $category) {
                    $selected = (int) $row->category_id === (int) $category->id ? 'selected' : '';
                    $options .= '<option value="' . $category->id . '" ' . $selected . '>' . e($category->name) . '</option>';
                }

                return '<select class="form-control form-control-sm select-kategori-inline" data-id="' . $row->id_barang . '">'
                    . $options
                    . '</select>';
            })
            ->editColumn('ket_barang', function ($row) {
                // Isinya HTML dari CKEditor -- dilucuti jadi teks polos di sini
                // (tabel ini menampilkannya sebagai preview singkat, bukan
                // deskripsi lengkap terformat seperti di halaman detail produk
                // toko/form edit), lalu dipotong sisi client (lihat nm_barang,
                // pola yang sama sudah dipakai di kolom itu).
                return trim(strip_tags((string) $row->ket_barang));
            })
            // HTML aslinya (BELUM dilucuti) dikirim terpisah, cuma dipakai modal
            // edit inline (CKEditor perlu markup aslinya, bukan versi preview yang
            // sudah dibuang tag-nya) -- tidak diikat ke kolom tabel manapun.
            ->addColumn('ket_barang_raw', fn ($row) => (string) $row->ket_barang)
            ->addColumn('checkbox', function ($row) {
                $checked = $row->status === 'active' ? 'checked' : '';
                return '<input type="checkbox" class="checkItem" value="' . $row->id_barang . '" ' . $checked . '>';
            })
            ->addColumn('gambar_status', function ($row) {
                if (!empty($row->image)) {
                    $url = asset('storage/' . $row->image);
                    return '<a href="' . $url . '" target="_blank" title="Lihat ukuran penuh">'
                        . '<img src="' . $url . '" alt="Gambar produk" class="img-thumbnail" '
                        . 'style="width:50px;height:50px;object-fit:cover;" '
                        . 'onerror="this.onerror=null;this.style.display=\'none\';this.insertAdjacentHTML(\'afterend\',\'<span class=&quot;badge bg-warning text-dark&quot;>File tidak ditemukan</span>\');">'
                        . '</a>';
                } else {
                    return '<span class="badge bg-danger">Belum upload</span>';
                }
            })
            ->addColumn('aksi', function ($row) use ($request) {
                // ambil parameter DataTables
                $start = $request->input('start', 0);
                $length = $request->input('length', 10);

                $btn = '<div class="dropdown position-relative d-inline-block">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
            ->rawColumns(['aksi', 'checkbox', 'gambar_status', 'kategori', 'ket_barang_raw'])
            ->make(true);
    }

    public function selectCategory()
    {
        $categories = Category::orderBy('name')->get();
        $uncategorized = request()->boolean('uncategorized');

        return view('backend.product.select-category', compact('categories', 'uncategorized'));
    }

    public function selectCategoryData(Request $request)
    {
        $query = Product::select([
            'id_barang',
            'kd_barang',
            'nm_barang',
            'sat_barang',
            'stok_barang',
            'image',
            'category_id',
        ]);

        if ($request->boolean('uncategorized')) {
            $query->where('category_id', 0)->where('stok_barang', '>', 0);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('gambar', function ($row) {
                if (!empty($row->image)) {
                    $url = asset('storage/' . $row->image);
                    return '<img src="' . $url . '" alt="Gambar produk" class="img-thumbnail" '
                        . 'style="width:50px;height:50px;object-fit:cover;" '
                        . 'onerror="this.onerror=null;this.src=\'\';this.alt=\'Gambar tidak ditemukan\';">';
                }
                return '<span class="badge bg-danger">Belum upload</span>';
            })
            ->rawColumns(['gambar'])
            ->make(true);
    }

    public function updateCategory(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $product->category_id = $request->category_id;
        $product->updated_by = Auth::id();
        $product->save();

        return response()->json(['message' => 'Kategori produk berhasil diperbarui.']);
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
            'ket_barang' => 'nullable|string', // Deskripsi produk (CKEditor, HTML)
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
        $product->ket_barang = $request->input('ket_barang', '');
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
