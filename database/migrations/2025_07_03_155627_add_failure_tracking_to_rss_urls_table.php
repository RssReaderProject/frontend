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
        Schema::table('rss_urls', function (Blueprint $table) {
            $table->unsignedInteger('consecutive_failures')->default(0)->after('user_id');
            $table->timestamp('last_failure_at')->nullable()->after('consecutive_failures');
            $table->timestamp('disabled_at')->nullable()->after('last_failure_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rss_urls', function (Blueprint $table) {
            $table->dropColumn(['consecutive_failures', 'last_failure_at', 'disabled_at']);
        });
    }
};
