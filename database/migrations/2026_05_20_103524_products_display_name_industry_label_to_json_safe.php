<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // temp JSON columns
            $table->json('display_name_json')->nullable()->after('display_name');
            $table->json('industry_label_json')->nullable()->after('industry_label');
        });

        // Backfill safely: string -> {"en": "<string>"} ; null/empty -> {}
        DB::statement("
            UPDATE products
            SET display_name_json = CASE
                WHEN display_name IS NULL OR TRIM(display_name) = '' THEN JSON_OBJECT()
                ELSE JSON_OBJECT('en', display_name)
            END
        ");

        DB::statement("
            UPDATE products
            SET industry_label_json = CASE
                WHEN industry_label IS NULL OR TRIM(industry_label) = '' THEN JSON_OBJECT()
                ELSE JSON_OBJECT('en', industry_label)
            END
        ");

        Schema::table('products', function (Blueprint $table) {
            // drop old string columns
            $table->dropColumn('display_name');
            $table->dropColumn('industry_label');
        });

        Schema::table('products', function (Blueprint $table) {
            // rename json columns into original names
            $table->renameColumn('display_name_json', 'display_name');
            $table->renameColumn('industry_label_json', 'industry_label');
        });
    }

    public function down(): void
    {
        // Reverse: json -> string (take en)
        Schema::table('products', function (Blueprint $table) {
            $table->string('display_name_str')->nullable()->after('display_name');
            $table->string('industry_label_str')->nullable()->after('industry_label');
        });

        DB::statement("
            UPDATE products
            SET display_name_str = JSON_UNQUOTE(JSON_EXTRACT(display_name, '$.en'))
        ");

        DB::statement("
            UPDATE products
            SET industry_label_str = JSON_UNQUOTE(JSON_EXTRACT(industry_label, '$.en'))
        ");

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('display_name');
            $table->dropColumn('industry_label');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('display_name_str', 'display_name');
            $table->renameColumn('industry_label_str', 'industry_label');
        });
    }
};