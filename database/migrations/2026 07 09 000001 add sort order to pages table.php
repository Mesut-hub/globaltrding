<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_published');
        });

        // Seed existing rows with sequential order by slug so nothing is 0/0/0
        $i = 1;
        foreach (DB::table('pages')->orderBy('slug')->pluck('id') as $id) {
            DB::table('pages')->where('id', $id)->update(['sort_order' => $i++]);
        }
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};