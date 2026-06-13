<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('active')->after('has_product_access')
                    ->comment('active | blocked | suspended');
            }
            if (! Schema::hasColumn('users', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('users', 'blocked_reason')) {
                $table->string('blocked_reason', 500)->nullable()->after('blocked_at');
            }
            if (! Schema::hasColumn('users', 'suspended_until')) {
                $table->timestamp('suspended_until')->nullable()->after('blocked_reason');
            }
            if (! Schema::hasColumn('users', 'suspended_reason')) {
                $table->string('suspended_reason', 500)->nullable()->after('suspended_until');
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('suspended_reason');
            }
            if (! Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }

            $table->index('status');
            $table->index(['status', 'has_product_access']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'has_product_access']);
            $table->dropColumn([
                'status', 'blocked_at', 'blocked_reason',
                'suspended_until', 'suspended_reason',
                'last_login_at', 'last_login_ip',
            ]);
        });
    }
};