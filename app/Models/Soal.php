<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    protected $table = 'soal';
    public $timestamps = false;

    protected $fillable = [
        'id_soal',
        'pertanyaan',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'jawaban_benar',
    ];

    public function header()
    {
        return $this->belongsTo(SoalHeader::class, 'id_soal', 'id_soal');
    }

    /**
     * Pertanyaan yang aman ditampilkan sebagai HTML, mengikuti render_pertanyaan_html()
     * di ujian.php: hanya sisakan tag format dasar; kalau teks polos tanpa tag,
     * pertahankan baris baru (nl2br) supaya tetap tampil per baris.
     */
    public function getPertanyaanHtmlAttribute(): string
    {
        $clean = strip_tags((string) $this->pertanyaan, '<p><br><strong><b><em><i><u><ol><ul><li><sub><sup><span><div>');

        if (strpos($clean, '<') === false) {
            return nl2br(e($clean), false);
        }

        return $clean;
    }
}
