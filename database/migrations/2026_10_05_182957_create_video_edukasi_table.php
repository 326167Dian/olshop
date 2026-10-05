<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema persis mengikuti `articles` (lihat create_articles_table) -- cuma
     * `content` (isi artikel) diganti `youtube_url` (link video), tanpa kolom
     * thumbnail upload karena thumbnail video diambil otomatis dari YouTube
     * (img.youtube.com/vi/{id}/hqdefault.jpg) saat ditampilkan, tidak perlu disimpan.
     */
    public function up(): void
    {
        Schema::create('video_edukasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('youtube_url');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_edukasi');
    }
};
