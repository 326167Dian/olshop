<?php

namespace App\Http\Controllers;

use App\Models\homepage;
use Illuminate\Http\Request;
use App\Models\JenisObat;
use App\Models\Product;
use App\Models\Category;
use App\Models\Article;
use App\Models\CompanySetting;
use App\Models\Banner;
use Faker\Provider\ar_EG\Company;
use Illuminate\Support\Facades\DB;

class HomepageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()

    {
        $companySetting = CompanySetting::first();
        $jenisobat = JenisObat::get();
        $kategori = Category::orderBy('name', 'desc')->get();
        $produkTerlaris = Product::where('status', 'active')->where('promosi', 'terlaris')->where('stok_barang', '>', 0)->take(4)->get();
        $databarang = Product::where('status', 'active')->where('stok_barang', '>', 0)->where('diskon', 0)->paginate(5);
        $diskonbarang = Product::where('status', 'active')->where('diskon', '>', 0)->where('stok_barang', '>', 0)->where('promosi', 'diskon')->paginate(5);
        $banners = Banner::where('status', 'active')->get();

        $articles = Article::where('status', 'published')->orderBy('created_at', 'desc')->paginate(3);
        return view('frontend.dashboard.index', compact('companySetting', 'databarang', 'produkTerlaris', 'jenisobat', 'kategori', 'articles', 'diskonbarang', 'banners'));
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
    public function show(homepage $homepage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(homepage $homepage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, homepage $homepage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(homepage $homepage)
    {
        //
    }
}
