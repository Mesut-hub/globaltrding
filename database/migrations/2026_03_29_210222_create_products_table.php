<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();

            $table->string('slug')->unique();

            $table->json('name');
            $table->json('summary')->nullable();
            $table->json('description')->nullable();

            $table->json('seo')->nullable();

            $table->boolean('is_published')->default(true);

            $table->timestamps();

            $table->index(['brand_id', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
