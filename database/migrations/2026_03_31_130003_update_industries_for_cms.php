<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('industries', function (Blueprint $table) {
            // Ensure these columns exist (and use your final names)
            if (! Schema::hasColumn('industries', 'cover_image_path')) {
                $table->string('cover_image_path')->nullable()->after('excerpt');
            }

            if (! Schema::hasColumn('industries', 'blocks')) {
                $table->json('blocks')->nullable()->after('cover_image_path');
            }

            if (! Schema::hasColumn('industries', 'is_published')) {
                $table->boolean('is_published')->default(true)->after('blocks');
            }

            if (! Schema::hasColumn('industries', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('is_published');
            }

            // Your table currently has title/excerpt columns but we must ensure they are JSON.
            // If they were created as string previously, change them to json in a separate migration
            // (DBAL is needed for change();). Since it's empty, easiest is to recreate them:

            // If your title/excerpt are NOT json already, do this manually:
            // 1) php artisan migrate:fresh (dev) OR
            // 2) create new columns title_tmp json, copy, drop old, rename.
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
