<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thread_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('number');
            $table->string('name', 32)->nullable();
            $table->string('trip', 12)->nullable();
            $table->text('body');
            // 発信者情報開示請求(プロバイダ責任制限法)への対応のため生IPを保持する。
            // ip_hashはレート制限・通報照合専用の一方向ハッシュで、通常のクエリでは生IPを使わない。
            $table->string('ip_address', 45);
            $table->string('ip_hash', 64);
            $table->timestamps();

            $table->unique(['thread_id', 'number']);
            $table->index(['thread_id', 'created_at']);
            $table->index('ip_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thread_posts');
    }
};
