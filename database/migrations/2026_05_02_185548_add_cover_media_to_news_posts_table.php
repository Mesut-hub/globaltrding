<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('news_posts', function (Blueprint $table) {
            $table->string('cover_image_path')->nullable()->after('excerpt');
            $table->string('cover_video_path')->nullable()->after('cover_image_path');
            $table->string('cover_poster_path')->nullable()->after('cover_video_path');
        });
    }

    public function down(): void
    {
        Schema::table('news_posts', function (Blueprint $table) {
            $table->dropColumn(['cover_image_path', 'cover_video_path', 'cover_poster_path']);
        });
    }
};