<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoEdukasi extends Model
{
    protected $table = 'video_edukasi';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'judul', 'slug', 'youtube_url', 'status'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id_admin');
    }

    /**
     * ID video (11 karakter) dari berbagai format URL YouTube yang umum dipakai
     * (watch?v=, youtu.be/, /embed/, /shorts/). Null kalau URL tidak dikenali --
     * tidak ada library/helper YouTube lain di app ini, ditulis dari nol.
     */
    public function getYoutubeIdAttribute(): ?string
    {
        return self::extractYoutubeId($this->youtube_url);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        $id = $this->youtube_id;

        return $id ? "https://www.youtube.com/embed/{$id}" : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $id = $this->youtube_id;

        return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : null;
    }

    public static function extractYoutubeId(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $pattern = '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';

        return preg_match($pattern, $url, $m) ? $m[1] : null;
    }
}
