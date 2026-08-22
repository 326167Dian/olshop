<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;



class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with('admin')->latest()->paginate(10);
        return view('backend.article.index', compact('articles'));
    }

    public function data(Request $request)
    {
        $query = Article::with('admin')->select([
            'id',
            'user_id',
            'title',
            'content',
            'slug',
            'thumbnail',
            'status'
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('admin_nama', function ($row) {
                return e($row->admin->nama_lengkap ?? '-');
            })
            ->editColumn('status', function (Article $article) {
                return ucfirst($article->status);
            })
            ->addColumn('aksi', function ($row) {
                $btn = '<div class="dropdown position-relative d-inline-block">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                    Action
                </button>
                <div class="dropdown-menu center-below p-2 shadow" style="min-width: 140px;">
                    <a href="' . route('article.edit', $row->id) . '" class="btn btn-warning btn-sm w-100 mb-1">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button onclick="deleteData(\'' . route('article.destroy', $row->id) . '\', this)" class="btn btn-danger btn-sm w-100" data-konf-delete="' . e($row->title) . '">
                        <i class="fa fa-trash"></i> Hapus
                    </button>
                </div>
            </div>';
                return $btn;
            })
            ->rawColumns(['aksi']) // penting agar tombol bisa dirender sebagai HTML
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.article.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'required|string|max:255|unique:articles,slug',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048',
            'status' => 'required|in:draft,published',
        ]);

        $article = new Article();
        $article->user_id = Auth::guard('admin')->id(); // gunakan guard admin
        $article->title = $request->title;
        $article->slug = Str::slug($request->title); // atau gunakan: Str::slug($request->title)
        $article->content = $request->content;
        $article->status = $request->status; // bisa diganti tergantung form

        if ($request->hasFile('thumbnail')) {
            // Pastikan folder thumbnails ada
            if (!Storage::disk('public')->exists('thumbnail')) {
                Storage::disk('public')->makeDirectory('thumbnail');
            }

            // Simpan file
            $path = $request->file('thumbnail')->store('thumbnail', 'public');
            $article->thumbnail = $path;
        }

        $article->save();

        return redirect()->route('article.index')->with('success', 'Artikel baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        return view('frontend.v_article.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        return view('backend.article.edit', compact('article'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'required|string|max:255|unique:articles,slug,' . $article->id,
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5048',
            'status' => 'required|in:draft,published',
        ]);

        $article->title = $request->title;
        $article->slug = $request->slug; // tetap manual/otomatis dari form
        $article->content = $request->content;
        $article->status = $request->status;

        if ($request->hasFile('thumbnail')) {
            if (!Storage::disk('public')->exists('thumbnail')) {
                Storage::disk('public')->makeDirectory('thumbnail');
            }

            if ($article->thumbnail && Storage::disk('public')->exists($article->thumbnail)) {
                Storage::disk('public')->delete($article->thumbnail);
            }

            $path = $request->file('thumbnail')->store('thumbnail', 'public');
            $article->thumbnail = $path;
        }

        $article->save();

        return redirect()->route('article.index')->with('success', 'Artikel berhasil diperbarui.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        if ($article->thumbnail && Storage::disk('public')->exists($article->thumbnail)) {
            Storage::disk('public')->delete($article->thumbnail);
        }

        $article->delete();

        return redirect()->route('article.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function indexFrontend()
    {
        $articles = Article::where('status', 'published')->orderBy('created_at', 'desc')->paginate(6);
        return view('frontend.v_article.index', compact('articles'));
    }
}
