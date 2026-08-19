<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostingRulesTest extends TestCase
{
    use RefreshDatabase;

    private function board(): Board
    {
        return Board::create([
            'name' => '雑談', 'slug' => 'zatsudan', 'description' => 'テスト板', 'position' => 1,
        ]);
    }

    private function thread(Board $board): Thread
    {
        $thread = $board->threads()->create([
            'title' => 'テストスレッド', 'reply_count' => 1, 'last_posted_at' => now()->subDay(),
        ]);
        $post = $thread->posts()->make(['number' => 1, 'body' => '最初の書き込みです。']);
        $post->ip_address = '127.0.0.1';
        $post->ip_hash = hash('sha256', '127.0.0.1');
        $post->save();

        return $thread;
    }

    public function test_出典としてURLを1つ貼った投稿は受け付ける(): void
    {
        $board = $this->board();

        $this->post(route('threads.store', $board), [
            'title' => 'この記事について',
            'body' => "この記事の内容が気になります。\nhttps://example.com/news/1",
        ])->assertRedirect();

        $this->assertDatabaseCount('threads', 1);
    }

    public function test_リンクが多すぎる投稿とURLだけの投稿は受け付けない(): void
    {
        $board = $this->board();
        $thread = $this->thread($board);

        $this->post(route('thread-posts.store', [$board, $thread]), [
            'body' => 'https://a.example.com https://b.example.com https://c.example.com',
        ])->assertSessionHasErrors('body');

        $this->post(route('thread-posts.store', [$board, $thread]), [
            'body' => 'https://a.example.com',
        ])->assertSessionHasErrors('body');

        $this->assertSame(1, $thread->fresh()->reply_count);
    }

    public function test_NG語を部分に含むだけの普通の言葉は弾かない(): void
    {
        $board = $this->board();

        $this->post(route('threads.store', $board), [
            'title' => 'カスタムPCの話',
            'body' => 'カスタマイズしたPCでバカンスの写真を整理しています。',
        ])->assertRedirect();

        $this->assertDatabaseCount('threads', 1);
    }

    public function test_NG語そのものは弾く(): void
    {
        $board = $this->board();

        $this->post(route('threads.store', $board), [
            'title' => 'テスト',
            'body' => 'お前なんか死ねばいい',
        ])->assertSessionHasErrors('body');

        $this->assertDatabaseCount('threads', 0);
    }

    public function test_sageを付けた返信はスレッドを上げない(): void
    {
        $board = $this->board();
        $thread = $this->thread($board);
        $before = $thread->last_posted_at;

        $this->post(route('thread-posts.store', [$board, $thread]), [
            'body' => 'sageで返信します。', 'sage' => '1',
        ])->assertRedirect();

        $thread->refresh();
        $this->assertSame(2, $thread->reply_count);
        $this->assertTrue($before->equalTo($thread->last_posted_at));
    }

    public function test_sage無しの返信はスレッドを上げる(): void
    {
        $board = $this->board();
        $thread = $this->thread($board);
        $before = $thread->last_posted_at;

        $this->post(route('thread-posts.store', [$board, $thread]), [
            'body' => '普通に返信します。',
        ])->assertRedirect();

        $this->assertTrue($thread->fresh()->last_posted_at->gt($before));
    }

    public function test_板内検索の結果はnoindexにする(): void
    {
        $board = $this->board();

        $this->get(route('boards.show', $board))
            ->assertOk()
            ->assertDontSee('name="robots"', false);

        $this->get(route('boards.show', $board).'?q=test')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false);
    }

    public function test_2ページ目は自分自身を正規URLとして申告する(): void
    {
        $board = $this->board();

        foreach (range(1, 31) as $i) {
            $board->threads()->create([
                'title' => "スレッド{$i}", 'reply_count' => 1, 'last_posted_at' => now(),
            ]);
        }

        $this->get(route('boards.show', $board).'?page=2')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('boards.show', $board).'?page=2">', false);
    }
}
