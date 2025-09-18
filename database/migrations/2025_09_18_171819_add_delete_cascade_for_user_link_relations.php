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
        Schema::table('links', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_user_id']);
            $table->foreign('submitted_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('user_links', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['link_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('link_id')->references('id')->on('links')->onDelete('cascade');
        });

        Schema::table('link_notes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['link_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('link_id')->references('id')->on('links')->onDelete('cascade');
        });

        Schema::table('user_statuses', function (Blueprint $table) {
            $table->dropForeign(['link_id']);
            $table->foreign('link_id')->references('id')->on('links')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_user_id']);
            $table->foreign('submitted_by_user_id')->references('id')->on('users');
        });

        Schema::table('user_links', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['link_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('link_id')->references('id')->on('links');
        });

        Schema::table('link_notes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['link_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('link_id')->references('id')->on('links');
        });

        Schema::table('user_statuses', function (Blueprint $table) {
            $table->dropForeign(['link_id']);
            $table->foreign('link_id')->references('id')->on('links')->onDelete('set null');
        });
    }
};
