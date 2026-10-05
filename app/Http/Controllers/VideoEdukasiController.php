<?php

namespace App\Http\Controllers;

use App\Models\VideoEdukasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

/**
 * Modul "Video Edukasi" -- mengikuti skema ArticleController persis (model
 * serupa, CRUD serupa, tampil di beranda mengikuti pola "Artikel Terbaru"),
 * cuma `content` diganti `youtube_url` (link YouTube, bukan teks/upload gambar
 * manual -- thumbnail diambil otomatis dari YouTube lewat
 * VideoEdukasi::thumbnail_url).
 */
class VideoEdukasiController extends Controller
{
    public function index()
    {
        $videos = VideoEdukasi::with('admin')->latest()->paginate(10);

        return view('backend.video-edukasi.index', compact('videos'));
    }

    public function data(Request $request)
    {
        $query = VideoEdukasi::with('admin')->select([
            'id', 'user_id', 'judul', 'slug', 'youtube_url', 'status',
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('admin_nama', function ($row) {
                return e($row->admin->nama_lengkap ?? '-');
            })
            ->editColumn('status', function (VideoEdukasi $video) {
                return ucfirst($video->status);
            })
            ->addColumn('aksi', function ($row) {
                return '<div class="d-flex flex-wrap gap-1">'
                    . '<a href="' . route('video-edukasi.edit', $row->id) . '" class="btn btn-warning btn-sm">'
                    . '<i class="fas fa-edit"></i> Edit</a>'
                    . '<button onclick="deleteData(\'' . route('video-edukasi.destroy', $row->id) . '\', this)" class="btn btn-danger btn-sm" data-konf-delete="' . e($row->judul) . '">'
                    . '<i class="fa fa-trash"></i> Hapus</button>'
                    . '</div>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        return view('backend.video-edukasi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'youtube_url' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
                if (!VideoEdukasi::extractYoutubeId($value)) {
                    $fail('Link YouTube tidak valid/tidak dikenali.');
                }
            }],
            'slug' => 'required|string|max:255|unique:video_edukasi,slug',
            'status' => 'required|in:draft,published',
        ]);

        VideoEdukasi::create([
            'user_id' => Auth::guard('admin')->id(),
            'judul' => $validated['judul'],
            'slug' => Str::slug($validated['judul']),
            'youtube_url' => $validated['youtube_url'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('video-edukasi.index')->with('success', 'Video edukasi baru berhasil ditambahkan.');
    }

    public function show($slug)
    {
        $video = VideoEdukasi::where('slug', $slug)->firstOrFail();

        return view('frontend.v_video_edukasi.show', compact('video'));
    }

    public function edit(VideoEdukasi $videoEdukasi)
    {
        return view('backend.video-edukasi.edit', ['video' => $videoEdukasi]);
    }

    public function update(Request $request, VideoEdukasi $videoEdukasi)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'youtube_url' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
                if (!VideoEdukasi::extractYoutubeId($value)) {
                    $fail('Link YouTube tidak valid/tidak dikenali.');
                }
            }],
            'slug' => 'required|string|max:255|unique:video_edukasi,slug,' . $videoEdukasi->id,
            'status' => 'required|in:draft,published',
        ]);

        $videoEdukasi->update($validated);

        return redirect()->route('video-edukasi.index')->with('success', 'Video edukasi berhasil diperbarui.');
    }

    public function destroy(VideoEdukasi $videoEdukasi)
    {
        $videoEdukasi->delete();

        return redirect()->route('video-edukasi.index')->with('success', 'Video edukasi berhasil dihapus.');
    }

    public function indexFrontend()
    {
        $videos = VideoEdukasi::where('status', 'published')->orderBy('created_at', 'desc')->paginate(6);

        return view('frontend.v_video_edukasi.index', compact('videos'));
    }
}
