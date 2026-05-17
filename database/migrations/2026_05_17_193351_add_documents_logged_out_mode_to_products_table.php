<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('products', function (Blueprint $table) {
      if (!Schema::hasColumn('products', 'pdp_documents_logged_out_mode')) {
        $table->string('pdp_documents_logged_out_mode')->default('list_disabled')
          ->after('pdp_public_documents'); // list_disabled|hide_all
      }
    });
  }

  public function down(): void
  {
    Schema::table('products', function (Blueprint $table) {
      if (Schema::hasColumn('products', 'pdp_documents_logged_out_mode')) {
        $table->dropColumn('pdp_documents_logged_out_mode');
      }
    });
  }
};