<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('registration_requests', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('registration_requests', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            if (Schema::hasColumn('registration_requests', 'reviewed_by')) {
                $table->dropConstrainedForeignId('reviewed_by');
            }
            if (Schema::hasColumn('registration_requests', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
        });
    }
};