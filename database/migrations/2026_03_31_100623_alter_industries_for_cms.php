<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('industries', function (Blueprint $table) {
            // If you already have image_path keep it, otherwise rename.
            if (!Schema::hasColumn('industries', 'cover_image_path') && Schema::hasColumn('industries', 'image_path')) {
                // optional rename; if you don’t want rename, skip this and use image_path everywhere
                // $table->renameColumn('image_path', 'cover_image_path');
            }

            if (!Schema::hasColumn('industries', 'blocks')) {
                $table->json('blocks')->nullable()->after('image_path'); // or after cover_image_path
            }

            // Ensure title/excerpt are JSON (if currently string, you'll need manual conversion in DB)
            // Best: add new json columns then later migrate data safely.
            if (!Schema::hasColumn('industries', 'title_json')) {
                $table->json('title_json')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('industries', 'excerpt_json')) {
                $table->json('excerpt_json')->nullable()->after('title_json');
            }
        });
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
