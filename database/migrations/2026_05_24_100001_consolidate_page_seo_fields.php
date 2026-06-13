<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        // Migrate meta_title + meta_description INTO seo JSON column
        // Then drop the redundant columns
        $pages = DB::table('pages')->get(['id', 'seo', 'meta_title', 'meta_description']);

        foreach ($pages as $page) {
            $seo = [];
            try {
                $seo = json_decode($page->seo ?? '{}', true) ?: [];
            } catch (\Throwable) {}

            $metaTitle = null;
            try {
                $metaTitle = json_decode($page->meta_title ?? 'null', true);
            } catch (\Throwable) {}

            $metaDesc = null;
            try {
                $metaDesc = json_decode($page->meta_description ?? 'null', true);
            } catch (\Throwable) {}

            // Merge: meta_title/meta_description win over old seo.title/description
            // (they were added later and are more accurate)
            if (is_array($metaTitle) && !empty($metaTitle)) {
                $seo['title'] = $metaTitle;
            }
            if (is_array($metaDesc) && !empty($metaDesc)) {
                $seo['description'] = $metaDesc;
            }

            DB::table('pages')->where('id', $page->id)->update([
                'seo' => json_encode($seo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        }

        // Drop redundant columns (data is now in seo JSON)
        Schema::table('pages', function (Blueprint $table) {
            if (Schema::hasColumn('pages', 'meta_title')) {
                $table->dropColumn('meta_title');
            }
            if (Schema::hasColumn('pages', 'meta_description')) {
                $table->dropColumn('meta_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('meta_title')->nullable()->after('title');
            $table->json('meta_description')->nullable()->after('meta_title');
        });
    }
};