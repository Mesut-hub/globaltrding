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
        Schema::create('market_instruments', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();      // usd-try, eur-try, brent, gold, etc.
            $table->string('evds_series')->nullable(); // EVDS series code e.g. TP.DK.USD.A (editable)
            $table->string('unit')->nullable();    // TRY, USD, etc.
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->json('name'); // multilingual label: {en: "...", tr: "...", ...}

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_instruments');
    }
};
