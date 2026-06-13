<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (! Schema::hasColumn('pages', 'show_in_company')) {
                $table->boolean('show_in_company')->default(false)->after('is_published');
            }
            if (! Schema::hasColumn('pages', 'show_in_products')) {
                $table->boolean('show_in_products')->default(false)->after('show_in_company');
            }
            if (! Schema::hasColumn('pages', 'show_in_information')) {
                $table->boolean('show_in_information')->default(false)->after('show_in_products');
            }
            if (! Schema::hasColumn('pages', 'show_in_service')) {
                $table->boolean('show_in_service')->default(false)->after('show_in_information');
            }

            // Keep show_in_footer if it exists (do NOT drop automatically).
            // Dropping columns requires doctrine/dbal in many setups; keep it stable.
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            foreach (['show_in_company', 'show_in_products', 'show_in_information', 'show_in_service'] as $col) {
                if (Schema::hasColumn('pages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};