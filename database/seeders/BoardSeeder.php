<?php

namespace Database\Seeders;

use App\Models\Board;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    public function run(): void
    {
        $boards = [
            ['name' => '雑談', 'slug' => 'zatsudan', 'description' => '何でも自由に話せる雑談板です。', 'position' => 1],
            ['name' => 'ニュース', 'slug' => 'news', 'description' => '時事ニュースについて語る板です。', 'position' => 2],
            ['name' => 'スポーツ', 'slug' => 'sports', 'description' => 'スポーツ全般について語る板です。', 'position' => 3],
            ['name' => 'アニメ・ゲーム', 'slug' => 'anime-game', 'description' => 'アニメ・ゲームについて語る板です。', 'position' => 4],
            ['name' => '芸能・エンタメ', 'slug' => 'entertainment', 'description' => '芸能・エンタメについて語る板です。', 'position' => 5],
            ['name' => '恋愛・人間関係', 'slug' => 'love-relationship', 'description' => '恋愛・人間関係の相談・雑談板です。', 'position' => 6],
            ['name' => '仕事・キャリア', 'slug' => 'work-career', 'description' => '仕事・キャリアについて語る板です。', 'position' => 7],
            ['name' => '質問・雑学', 'slug' => 'question-trivia', 'description' => '疑問・雑学を投稿・回答する板です。', 'position' => 8],
            ['name' => '趣味', 'slug' => 'hobby', 'description' => '趣味全般について語る板です。', 'position' => 9],
            ['name' => '暇つぶし', 'slug' => 'himatsubushi', 'description' => '気軽な暇つぶし雑談板です。', 'position' => 10],
        ];

        foreach ($boards as $board) {
            Board::updateOrCreate(['slug' => $board['slug']], $board);
        }
    }
}
