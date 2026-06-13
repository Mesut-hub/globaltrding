<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            $table->json('label');
            $table->string('url')->nullable(); // direct URL (can include {locale})
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();

            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->enum('target', ['_self', '_blank'])->default('_self');

            $table->timestamps();

            $table->index(['parent_id', 'sort_order', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};