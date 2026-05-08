<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nav_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nav_group_id')->constrained('nav_groups')->cascadeOnDelete();

            $table->json('label'); // translatable
            $table->string('url')->nullable(); // can include {locale}
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->enum('target', ['_self', '_blank'])->default('_self');

            // optional “action” hook for overlay buttons (finder/search/etc.)
            $table->string('action')->nullable(); // e.g. finder, search

            $table->timestamps();

            $table->index(['nav_group_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_links');
    }
};