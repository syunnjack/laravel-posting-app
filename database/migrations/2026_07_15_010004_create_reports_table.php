<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('thread_post_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 20); // 'report' (通報) | 'removal_request' (削除依頼)
            $table->string('reporter_email')->nullable();
            $table->text('reason');
            $table->string('status', 20)->default('pending'); // 'pending' | 'resolved'
            $table->string('ip_hash', 64);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
