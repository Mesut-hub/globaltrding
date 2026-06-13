<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('show_in_company')->default(false)->after('show_in_footer');
            $table->boolean('show_in_products')->default(false)->after('show_in_company');
            $table->boolean('show_in_information')->default(false)->after('show_in_products');
            $table->boolean('show_in_service')->default(false)->after('show_in_information');

            if (Schema::hasColumn('pages', 'show_in_footer')) {
                $table->dropColumn('show_in_footer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('show_in_footer')->default(false);

            $table->dropColumn([
                'show_in_company',
                'show_in_products',
                'show_in_information',
                'show_in_service',
            ]);
        });
    }
};