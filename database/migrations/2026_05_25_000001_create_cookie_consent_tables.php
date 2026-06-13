<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Cookie categories (necessary, analytics, marketing, social)
        Schema::create('cookie_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique(); // necessary, analytics, marketing, social
            $table->json('label');               // {"en":"Analytics","tr":"Analitik",...}
            $table->json('description');
            $table->boolean('is_required')->default(false); // necessary = always on
            $table->boolean('is_enabled')->default(true);   // admin can disable a category
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Cookie consent logs (GDPR audit trail)
        Schema::create('cookie_consent_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 128)->index();
            $table->string('ip_hash', 64)->nullable();       // SHA-256 of IP, not raw IP
            $table->json('consents');                        // {"analytics":true,"marketing":false,...}
            $table->string('locale', 8)->default('en');
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamp('consented_at');
            $table->string('consent_version', 16)->default('1.0'); // bump when policy changes
            $table->timestamps();
            $table->index(['session_id', 'consented_at']);
        });

        // Cookie banner content (editable via admin)
        Schema::create('cookie_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->json('value');  // multilingual or scalar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_settings');
        Schema::dropIfExists('cookie_consent_logs');
        Schema::dropIfExists('cookie_categories');
    }
};