<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_statuses', function (Blueprint $table) {
            $table->foreignId('link_id')->nullable()->constrained('links')->onDelete('set null');
            $table->index(['link_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('user_statuses', function (Blueprint $table) {
            $table->dropForeign(['link_id']);
            $table->dropIndex(['link_id', 'created_at']);
            $table->dropColumn('link_id');
        });
    }
};
