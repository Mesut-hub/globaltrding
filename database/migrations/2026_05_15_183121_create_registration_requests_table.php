<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();

            // Step 1
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();;
            $table->string('occupation')->nullable();
            $table->string('mobile_phone');
            $table->string('primary_product_interest')->nullable();
            $table->string('preferred_language')->default('English');
            $table->boolean('accepted_terms')->default(false);

            // Step 2
            $table->string('company');
            $table->boolean('existing_customer')->nullable();
            $table->string('location')->nullable();     // country/region
            $table->string('city');
            $table->string('street_and_number');
            $table->string('zip_code');
            $table->string('industries_operate')->nullable();
            $table->text('message')->nullable();

            // Meta
            $table->string('status')->default('submitted'); // submitted|reviewed|approved|rejected
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
    }
};