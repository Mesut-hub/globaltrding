<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nav_links', function (Blueprint $table) {
            if (!Schema::hasColumn('nav_links', 'desc')) {
                $table->text('desc')->nullable()->after('action');
            }
            if (!Schema::hasColumn('nav_links', 'preview_image')) {
                $table->string('preview_image')->nullable()->after('desc'); // public path or absolute URL
            }
            if (!Schema::hasColumn('nav_links', 'is_finder')) {
                $table->boolean('is_finder')->default(false)->after('preview_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nav_links', function (Blueprint $table) {
            $table->dropColumn(['desc', 'preview_image', 'is_finder']);
        });
    }
};