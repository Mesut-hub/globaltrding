<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('collaboration_requests', function (Blueprint $table) {
            $table->string('company')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('country')->nullable(false)->change();
            $table->text('message')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('collaboration_requests', function (Blueprint $table) {
            $table->string('company')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->text('message')->nullable()->change();
        });
    }
};