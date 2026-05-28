<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Action constants are defined on CustomerActivityLog model
            $table->string('action', 60)->index();
            // e.g. login | logout | login_failed | blocked | unblocked |
            //      suspended | unsuspended | access_granted | access_revoked |
            //      force_logout | password_reset_sent | deleted

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->json('context')->nullable();
            // Stores extra data: reason, duration, old_status → new_status, etc.

            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            // NULL = system/self; non-null = admin who performed the action

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_activity_logs');
    }
};