<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('collaboration_requests', function (Blueprint $table) {
            $table->timestamp('replied_at')->nullable()->after('reviewed_by');
            $table->longText('reply_body')->nullable()->after('replied_at');
        });
    }

    public function down(): void
    {
        Schema::table('collaboration_requests', function (Blueprint $table) {
            $table->dropColumn(['replied_at', 'reply_body']);
        });
    }
};