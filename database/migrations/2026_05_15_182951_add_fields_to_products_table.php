<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Admin-simplified name + target URL (hyperlinked name)
            $table->string('display_name')->nullable()->after('slug');
            $table->string('display_url')->nullable()->after('display_name');

            // BASF-like meta
            $table->string('prd_number')->nullable()->after('display_url');
            $table->string('industry_label')->nullable()->after('prd_number');

            // Finder filter facets (store as JSON arrays of strings for now)
            $table->json('industries')->nullable()->after('industry_label');
            $table->json('applications')->nullable()->after('industries');
            $table->json('product_groups')->nullable()->after('applications');
            $table->json('processes')->nullable()->after('product_groups');
            $table->json('sustainability_tags')->nullable()->after('processes');
            $table->json('regulatory_tags')->nullable()->after('sustainability_tags');

            // PDP content (BASF sections)
            $table->longText('pdp_overview_html')->nullable()->after('regulatory_tags');
            $table->longText('pdp_properties_html')->nullable()->after('pdp_overview_html');
            $table->json('pdp_documents')->nullable()->after('pdp_properties_html'); // [{title,url,language,category}]
            $table->longText('pdp_sustainability_html')->nullable()->after('pdp_documents');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'display_name','display_url','prd_number','industry_label',
                'industries','applications','product_groups','processes','sustainability_tags','regulatory_tags',
                'pdp_overview_html','pdp_properties_html','pdp_documents','pdp_sustainability_html',
            ]);
        });
    }
};