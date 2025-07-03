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
            // Add unique index to prevent duplicate items for the same user
            $table->unique(['user_id', 'link'], 'rss_items_user_link_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rss_items', function (Blueprint $table) {
            $table->dropUnique('rss_items_user_link_unique');
        });
    }
}; 