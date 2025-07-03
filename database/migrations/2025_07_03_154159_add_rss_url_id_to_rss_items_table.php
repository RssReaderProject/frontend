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
        Schema::table('rss_items', function (Blueprint $table) {
            $table->foreignId('rss_url_id')->nullable()->constrained()->onDelete('cascade');
            $table->index(['user_id', 'rss_url_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rss_items', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'rss_url_id']);
            $table->dropForeign(['rss_url_id']);
            $table->dropColumn('rss_url_id');
        });
    }
};
