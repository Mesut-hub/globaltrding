<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inquiry_replies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inquiry_request_id')
                ->constrained('inquiry_requests')
                ->cascadeOnDelete();

            $table->string('to_email');
            $table->string('subject');
            $table->longText('body');

            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['inquiry_request_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_replies');
    }
};