<?php
// database/migrations/2026_05_28_000001_create_promotions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();

            // ── Translatable content ────────────────────────────────────────
            $table->json('title');                          // {"en":"...","tr":"...","ar":"...","fr":"..."}
            $table->json('content')->nullable();            // promotional body text
            $table->json('cta_label')->nullable();          // call-to-action button text
            $table->string('cta_url', 2048)->nullable();
            $table->string('cta_target', 10)->default('_self'); // _self | _blank

            // ── Media ───────────────────────────────────────────────────────
            $table->string('media_type', 20)->default('none'); // none | image | video
            $table->string('media_path', 2048)->nullable();
            $table->string('thumbnail_path', 2048)->nullable(); // video poster / fallback image

            // ── Display behaviour ───────────────────────────────────────────
            $table->string('animation_type', 30)->default('slide_up');
            // fade | slide_up | slide_down | zoom
            $table->string('display_mode', 20)->default('manual');
            // manual = header button click | auto = page-load trigger
            $table->string('display_frequency', 30)->default('once_per_session');
            // always | once_per_session | once_per_day | once_per_week | once_ever
            $table->unsignedInteger('auto_show_delay_ms')->default(2500);

            // ── Overlay appearance ──────────────────────────────────────────
            $table->string('overlay_size', 10)->default('md');   // sm | md | lg | xl | full
            $table->string('overlay_position', 20)->default('center');
            // center | bottom | bottom-left | bottom-right
            $table->string('bg_color', 20)->default('#ffffff');
            $table->string('text_color', 20)->default('#0f172a');
            $table->string('cta_bg_color', 20)->default('#0f172a');
            $table->string('cta_text_color', 20)->default('#ffffff');
            $table->boolean('show_close_button')->default(true);
            $table->boolean('close_on_backdrop')->default(true);

            // ── Targeting ───────────────────────────────────────────────────
            // null = all pages. JSON array of URL patterns: ["*"] or ["/en/products*"]
            $table->json('target_pages')->nullable();

            // ── Scheduling & priority ───────────────────────────────────────
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->smallInteger('priority')->default(0); // higher = shown first

            $table->timestamps();

            $table->index(['is_active', 'priority']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};