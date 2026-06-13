<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Ensure columns exist
        Schema::table('industries', function (Blueprint $table) {
            if (! Schema::hasColumn('industries', 'excerpt')) {
                $table->json('excerpt')->nullable()->after('title');
            }

            if (! Schema::hasColumn('industries', 'cover_image_path') && Schema::hasColumn('industries', 'image_path')) {
                // If you prefer cover_image_path, uncomment rename and update code accordingly
                // $table->renameColumn('image_path', 'cover_image_path');
            }

            if (! Schema::hasColumn('industries', 'blocks')) {
                $table->json('blocks')->nullable()->after('cover_image_path');
            }

            if (! Schema::hasColumn('industries', 'is_published')) {
                $table->boolean('is_published')->default(true);
            }

            if (! Schema::hasColumn('industries', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0);
            }
        });

        // 2) If title/excerpt are TEXT/VARCHAR, we’ll convert existing plain strings into {"en": "..."} JSON.
        // This is safe even if already JSON-looking.
        $defaultLocale = config('locales.default', 'en');

        // Convert title
        $rows = DB::table('industries')->select('id', 'title')->get();
        foreach ($rows as $r) {
            if ($r->title === null) continue;

            $isJson = false;
            if (is_string($r->title)) {
                $trim = trim($r->title);
                $isJson = ($trim !== '' && ($trim[0] === '{' || $trim[0] === '['));
            }

            if (! $isJson) {
                DB::table('industries')->where('id', $r->id)->update([
                    'title' => json_encode([$defaultLocale => (string) $r->title], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }

        // Convert excerpt (if it exists and has values)
        if (Schema::hasColumn('industries', 'excerpt')) {
            $rows = DB::table('industries')->select('id', 'excerpt')->get();
            foreach ($rows as $r) {
                if ($r->excerpt === null) continue;

                $isJson = false;
                if (is_string($r->excerpt)) {
                    $trim = trim($r->excerpt);
                    $isJson = ($trim !== '' && ($trim[0] === '{' || $trim[0] === '['));
                }

                if (! $isJson) {
                    DB::table('industries')->where('id', $r->id)->update([
                        'excerpt' => json_encode([$defaultLocale => (string) $r->excerpt], JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('industries', function (Blueprint $table) {
            //
        });
    }
};
