<?php

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
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
        Schema::create('user_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('link_id')->constrained();
            $table->string('category')->default(LinkCategory::READ->value);
            $table->string('status')->default(LinkStatus::UNREAD->value);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'link_id']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_links');
    }
};
