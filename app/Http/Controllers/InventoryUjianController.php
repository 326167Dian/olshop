<?php

namespace App\Http\Controllers;

use App\Models\HasilUjian;
use App\Models\Soal;
use App\Models\SoalHeader;
use App\Models\UjianProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryUjianController extends Controller
{
    /**
     * Modul "Ujian" (module=ujian), mengikuti
     * public/apotekberlian/masuk/modul/mod_ujian/{ujian,aksi_ujian,proses,autosave}.php.
     * Seluruh modul digerbang flag 'ujian' (siapa saja yang login dan punya flag ini
     * boleh MENGERJAKAN ujian), tapi fitur kelola soal & lihat hasil hanya untuk
     * pemilik -- sama seperti gerbang $_SESSION['level']=='pemilik' di legacy.
     * "Ujian aktif" per-sesi PHP ($_SESSION['ujian_aktif_id']) tidak direplikasi --
     * navigasi murni lewat query string/dropdown, sudah cukup dan lebih stateless.
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $daftarUjian = SoalHeader::orderByDesc('id_soal')->get();

        $selectedId = (int) $request->query('ujian_id', 0);
        if ($selectedId <= 0 && $daftarUjian->isNotEmpty()) {
            $selectedId = $daftarUjian->first()->id_soal;
        }

        $ujianAktif = $selectedId > 0 ? $daftarUjian->firstWhere('id_soal', $selectedId) : null;
        $daftarSoal = [];

        if ($ujianAktif) {
            $daftarSoal = Soal::where('id_soal', $selectedId)->orderBy('id')->get();
            $daftarSoal = $daftarSoal->shuffle()->values();
        }

        $durasiMenit = ($ujianAktif && $ujianAktif->durasi > 0) ? (int) $ujianAktif->durasi : 15;

        return view('inventory.ujian.index', [
            'judul' => 'Inventory',
            'isPemilik' => $admin->isPemilik(),
            'daftarUjian' => $daftarUjian,
            'selectedId' => $selectedId,
            'ujianAktif' => $ujianAktif,
            'daftarSoal' => $daftarSoal,
            'durasiMenit' => $durasiMenit,
        ]);
    }

    /**
     * Autosave progres jawaban (AJAX), mengikuti autosave.php.
     */
    public function autosave(Request $request)
    {
        $validated = $request->validate([
            'ujian_id' => 'required|integer|exists:soal_header,id_soal',
            'jawaban' => 'nullable|array',
            'exam_started_at' => 'nullable|integer',
        ]);

        $admin = Auth::guard('admin')->user();
        $ujian = SoalHeader::find($validated['ujian_id']);
        $jawaban = collect($validated['jawaban'] ?? [])->mapWithKeys(fn ($v, $k) => [(int) $k => (string) $v])->all();

        $waktuMulai = !empty($validated['exam_started_at'])
            ? \Carbon\Carbon::createFromTimestamp($validated['exam_started_at'])
            : now();

        UjianProgress::updateOrCreate(
            ['id_admin' => $admin->id_admin, 'ujian_id' => $validated['ujian_id']],
            [
                'username' => $admin->username,
                'nama_lengkap' => $admin->nama_lengkap,
                'nama_ujian' => $ujian->nm_ujian ?? null,
                'jawaban_json' => $jawaban,
                'waktu_mulai' => $waktuMulai,
                'waktu_update' => now(),
            ]
        );

        return response()->json(['ok' => true, 'terjawab' => count($jawaban)]);
    }

    /**
     * Kirim & nilai jawaban ujian, mengikuti proses.php.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'ujian_id' => 'required|integer|exists:soal_header,id_soal',
            'jawaban' => 'required|array',
            'exam_started_at' => 'nullable|integer',
            'exam_duration_seconds' => 'nullable|integer',
        ]);

        $admin = Auth::guard('admin')->user();
        $ujianId = (int) $validated['ujian_id'];
        $jawabanUser = collect($validated['jawaban'])->mapWithKeys(fn ($v, $k) => [(int) $k => strtolower(trim((string) $v))]);

        $namaUjian = SoalHeader::find($ujianId)->nm_ujian ?? '';

        $kunciJawaban = Soal::where('id_soal', $ujianId)
            ->whereIn('id', $jawabanUser->keys())
            ->pluck('jawaban_benar', 'id')
            ->map(fn ($v) => strtolower(trim((string) $v)));

        $benar = 0;
        $salah = 0;
        $tidakValid = 0;

        foreach ($jawabanUser as $id => $jawab) {
            if (!$kunciJawaban->has($id)) {
                $tidakValid++;
                continue;
            }

            if ($jawab === $kunciJawaban->get($id)) {
                $benar++;
            } else {
                $salah++;
            }
        }

        $totalDinilai = $benar + $salah;
        $totalSoal = Soal::where('id_soal', $ujianId)->count();
        if ($totalSoal <= 0) {
            $totalSoal = $totalDinilai;
        }

        $tidakDijawab = max(0, $totalSoal - $totalDinilai);
        $nilaiAkhir = $totalSoal > 0 ? round(($benar / $totalSoal) * 100, 2) : 0;

        $waktuSelesai = now();
        $examStartedAt = (int) ($validated['exam_started_at'] ?? 0);
        $waktuMulai = $examStartedAt > 0 ? now()->createFromTimestamp($examStartedAt) : $waktuSelesai;
        $durasiDetik = max(0, $waktuSelesai->diffInSeconds($waktuMulai));
        $durasiBatas = (int) ($validated['exam_duration_seconds'] ?? 0);
        $statusWaktu = ($durasiBatas > 0 && $durasiDetik > $durasiBatas) ? 'timeout' : 'on_time';

        $hasil = HasilUjian::create([
            'id_admin' => $admin->id_admin,
            'username' => $admin->username,
            'nama_lengkap' => $admin->nama_lengkap,
            'ujian_id' => $ujianId,
            'nama_ujian' => $namaUjian !== '' ? $namaUjian : null,
            'total_soal' => $totalSoal,
            'jawaban_benar' => $benar,
            'jawaban_salah' => $salah,
            'tidak_dijawab' => $tidakDijawab,
            'soal_tidak_valid' => $tidakValid,
            'nilai_akhir' => $nilaiAkhir,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'durasi_detik' => $durasiDetik,
            'durasi_batas_detik' => $durasiBatas > 0 ? $durasiBatas : null,
            'status_waktu' => $statusWaktu,
            'jawaban_json' => $jawabanUser->all(),
        ]);

        UjianProgress::where('id_admin', $admin->id_admin)->where('ujian_id', $ujianId)->delete();

        return view('inventory.ujian.hasil-kirim', [
            'judul' => 'Inventory',
            'hasil' => $hasil,
            'tidakValid' => $tidakValid,
        ]);
    }

    /**
     * Kelola Soal Ujian (header + daftar soal), mengikuti case 'kelola'. Hanya pemilik.
     */
    public function kelola(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403, 'Fitur CRUD soal hanya untuk status pemilik.');

        $daftarUjian = SoalHeader::orderByDesc('id_soal')->get();
        $editHeaderId = (int) $request->query('edit_header_id', 0);
        $headerEdit = $editHeaderId > 0 ? SoalHeader::find($editHeaderId) : null;

        $selectedId = (int) $request->query('ujian_id', 0);
        $daftarSoal = Soal::with('header')->orderBy('id_soal')->orderBy('id')->get();

        return view('inventory.ujian.kelola', [
            'judul' => 'Inventory',
            'daftarUjian' => $daftarUjian,
            'headerEdit' => $headerEdit,
            'daftarSoal' => $daftarSoal,
            'selectedId' => $selectedId,
        ]);
    }

    public function headerStore(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $validated = $request->validate([
            'nm_ujian' => 'required|string|max:100',
            'durasi' => 'required|integer|min:1',
        ]);

        $header = SoalHeader::create($validated);

        return redirect()->route('inventory.ujian.soal.create', ['ujian_id' => $header->id_soal])
            ->with('success', 'Ujian berhasil disimpan.');
    }

    public function headerUpdate(Request $request, SoalHeader $soalHeader)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $validated = $request->validate([
            'nm_ujian' => 'required|string|max:100',
            'durasi' => 'required|integer|min:1',
        ]);

        $soalHeader->update($validated);

        return redirect()->route('inventory.ujian.kelola', ['ujian_id' => $soalHeader->id_soal])
            ->with('success', 'Ujian berhasil diperbarui.');
    }

    public function soalCreate(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $daftarUjian = SoalHeader::orderByDesc('id_soal')->get();
        $prefillId = (int) $request->query('ujian_id', 0);
        $prefillUjian = $prefillId > 0 ? $daftarUjian->firstWhere('id_soal', $prefillId) : $daftarUjian->first();

        return view('inventory.ujian.soal-create', [
            'judul' => 'Inventory',
            'prefillUjian' => $prefillUjian,
        ]);
    }

    public function soalStore(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $validated = $request->validate([
            'id_soal' => 'required|integer|exists:soal_header,id_soal',
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string|max:255',
            'opsi_b' => 'required|string|max:255',
            'opsi_c' => 'required|string|max:255',
            'jawaban_benar' => 'required|in:a,b,c',
        ]);

        Soal::create($validated);

        return redirect()->route('inventory.ujian.soal.create', ['ujian_id' => $validated['id_soal']])
            ->with('success', 'Soal berhasil disimpan.');
    }

    public function soalEdit(Soal $soal)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        return view('inventory.ujian.soal-edit', [
            'judul' => 'Inventory',
            'soal' => $soal,
            'daftarUjian' => SoalHeader::orderByDesc('id_soal')->get(),
        ]);
    }

    public function soalUpdate(Request $request, Soal $soal)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $validated = $request->validate([
            'id_soal' => 'required|integer|exists:soal_header,id_soal',
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string|max:255',
            'opsi_b' => 'required|string|max:255',
            'opsi_c' => 'required|string|max:255',
            'jawaban_benar' => 'required|in:a,b,c',
        ]);

        $soal->update($validated);

        return redirect()->route('inventory.ujian.kelola', ['ujian_id' => $soal->id_soal])
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function soalDestroy(Soal $soal)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $ujianId = $soal->id_soal;
        $soal->delete();

        return redirect()->route('inventory.ujian.kelola', ['ujian_id' => $ujianId])
            ->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Hasil Akhir Ujian, mengikuti case 'hasilujian'. Hanya pemilik.
     */
    public function hasil(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $daftarUjian = SoalHeader::orderByDesc('id_soal')->get();
        $selectedId = (int) $request->query('ujian_id', 0);

        $queryHasil = HasilUjian::query();
        if ($selectedId > 0) {
            $queryHasil->where('ujian_id', $selectedId);
        }
        $daftarHasil = $queryHasil->orderByDesc('id_hasil')->limit(500)->get();

        $queryProgress = UjianProgress::query();
        if ($selectedId > 0) {
            $queryProgress->where('ujian_id', $selectedId);
        }
        $progressRows = $queryProgress->orderByDesc('waktu_update')->limit(500)->get();

        $daftarProgress = $progressRows->map(function (UjianProgress $p) {
            $totalSoal = Soal::where('id_soal', $p->ujian_id)->count();

            return [
                'nama_lengkap' => $p->nama_lengkap,
                'nama_ujian' => $p->nama_ujian,
                'terjawab' => is_array($p->jawaban_json) ? count($p->jawaban_json) : 0,
                'total_soal' => $totalSoal,
                'waktu_mulai' => $p->waktu_mulai,
                'waktu_update' => $p->waktu_update,
            ];
        });

        return view('inventory.ujian.hasil', [
            'judul' => 'Inventory',
            'daftarUjian' => $daftarUjian,
            'selectedId' => $selectedId,
            'daftarHasil' => $daftarHasil,
            'daftarProgress' => $daftarProgress,
        ]);
    }

    /**
     * Detail hasil satu peserta, mengikuti case 'detailhasil'. Hanya pemilik.
     */
    public function hasilDetail(HasilUjian $hasil)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $jawabanUser = is_array($hasil->jawaban_json) ? $hasil->jawaban_json : [];
        $daftarSoal = Soal::where('id_soal', $hasil->ujian_id)->orderBy('id')->get();

        return view('inventory.ujian.hasil-detail', [
            'judul' => 'Inventory',
            'hasil' => $hasil,
            'daftarSoal' => $daftarSoal,
            'jawabanUser' => $jawabanUser,
        ]);
    }
}
