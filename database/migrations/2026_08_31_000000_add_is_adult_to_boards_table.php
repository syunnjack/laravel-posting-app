<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 成人向けの板を区別する。
     *
     * 既存の12板は全年齢向けで、サイト全体に年齢確認が無い。
     * 成人向けを同じ一覧に並べると、年齢確認なしで誰でも辿れてしまう。
     * この列で分け、閲覧前に年齢確認を挟み、sitemap と検索からは外す。
     */
    public function up(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->boolean('is_adult')->default(false)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn('is_adult');
        });
    }
};
