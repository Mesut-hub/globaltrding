<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            if (! Schema::hasColumn('home_sections', 'title')) {
                $table->json('title')->nullable()->after('key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            if (Schema::hasColumn('home_sections', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};