<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Thread;
use App\Models\ThreadPost;
use App\Support\ContentModeration;
use App\Support\TripCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThreadController extends Controller
{
    public function create(Board $board)
    {
        return view('threads.create', compact('board'));
    }

    public function store(Request $request, Board $board)
    {
        // ハニーポット: ボットはこの隠しフィールドを埋めてしまう
        if (! empty($request->input('website'))) {
            return redirect()->route('boards.show', $board);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|min:2|max:2000',
            'name' => 'nullable|string|max:64',
        ]);

        if (ContentModeration::containsNgWord($validated['title'] . ' ' . $validated['body'])) {
            return back()->withErrors(['body' => '投稿内容に不適切な可能性のある表現が含まれています。内容をご確認のうえ再度投稿してください。'])->withInput();
        }

        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("thread_create:{$ipHash}", 45)) {
            return back()->withErrors(['body' => '投稿間隔が短すぎます。しばらく待ってから再度お試しください。'])->withInput();
        }

        [$name, $trip] = $this->parseName($validated['name'] ?? '');

        $thread = DB::transaction(function () use ($board, $validated, $name, $trip, $request) {
            $thread = $board->threads()->create([
                'title' => $validated['title'],
                'reply_count' => 1,
                'last_posted_at' => now(),
            ]);

            $post = $thread->posts()->make([
                'number' => 1,
                'name' => $name,
                'trip' => $trip,
                'body' => $validated['body'],
            ]);
            $post->ip_address = ContentModeration::clientIp($request);
            $post->ip_hash = ContentModeration::clientIpHash($request);
            $post->save();

            return $thread;
        });

        return redirect()->route('threads.show', [$board, $thread]);
    }

    public function show(Board $board, Thread $thread)
    {
        $posts = $thread->posts()->paginate(50);

        return view('threads.show', compact('board', 'thread', 'posts'));
    }

    /**
     * 名前欄を「名前#トリップ用秘密鍵」の形式でパースする。
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function parseName(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [null, null];
        }

        $parts = preg_split('/[#＃]/u', $raw, 2);
        $name = $parts[0] !== '' ? $parts[0] : null;
        $trip = isset($parts[1]) && $parts[1] !== '' ? TripCode::generate($parts[1]) : null;

        return [$name, $trip];
    }
}
