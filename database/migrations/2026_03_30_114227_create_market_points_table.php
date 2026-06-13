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
        Schema::create('market_points', function (Blueprint $table) {
            $table->id();

            $table->foreignId('market_instrument_id')->constrained()->cascadeOnDelete();

            $table->date('date');
            $table->decimal('value', 18, 6)->nullable();

            $table->timestamps();

            $table->unique(['market_instrument_id', 'date']);
            $table->index(['date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_points');
    }
};
