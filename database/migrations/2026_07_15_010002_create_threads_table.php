<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->onDelete('cascade');
            $table->string('title', 100);
            $table->unsignedInteger('reply_count')->default(1);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('last_posted_at')->nullable();
            $table->timestamps();

            $table->index(['board_id', 'last_posted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};
