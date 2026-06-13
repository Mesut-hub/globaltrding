<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('products', function(Blueprint $table){
      $table->json('pdp_overview_blocks')->nullable()->after('pdp_sustainability_html');
      $table->json('pdp_properties_blocks')->nullable()->after('pdp_overview_blocks');
      $table->json('pdp_documents_blocks')->nullable()->after('pdp_properties_blocks');
      $table->json('pdp_sustainability_blocks')->nullable()->after('pdp_documents_blocks');

      $table->boolean('pdp_public_overview')->default(true)->after('pdp_sustainability_blocks');
      $table->boolean('pdp_public_properties')->default(false)->after('pdp_public_overview');
      $table->boolean('pdp_public_documents')->default(false)->after('pdp_public_properties');
      $table->boolean('pdp_public_sustainability')->default(true)->after('pdp_public_documents');
    });
  }

  public function down(): void
  {
    Schema::table('products', function(Blueprint $table){
      $table->dropColumn([
        'pdp_overview_blocks','pdp_properties_blocks','pdp_documents_blocks','pdp_sustainability_blocks',
        'pdp_public_overview','pdp_public_properties','pdp_public_documents','pdp_public_sustainability',
      ]);
    });
  }
};