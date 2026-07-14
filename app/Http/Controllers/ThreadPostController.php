<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Thread;
use App\Support\ContentModeration;
use App\Support\TripCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThreadPostController extends Controller
{
    public function store(Request $request, Board $board, Thread $thread)
    {
        if ($thread->is_locked) {
            return back()->withErrors(['body' => 'このスレッドはロックされているため返信できません。']);
        }

        // ハニーポット: ボットはこの隠しフィールドを埋めてしまう
        if (! empty($request->input('website'))) {
            return redirect()->route('threads.show', [$board, $thread]);
        }

        $validated = $request->validate([
            'body' => 'required|string|min:1|max:2000',
            'name' => 'nullable|string|max:64',
        ]);

        if (ContentModeration::containsNgWord($validated['body'])) {
            return back()->withErrors(['body' => '投稿内容に不適切な可能性のある表現が含まれています。内容をご確認のうえ再度投稿してください。'])->withInput();
        }

        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("thread_reply:{$ipHash}", 30)) {
            return back()->withErrors(['body' => '投稿間隔が短すぎます。しばらく待ってから再度お試しください。'])->withInput();
        }

        [$name, $trip] = $this->parseName($validated['name'] ?? '');

        DB::transaction(function () use ($thread, $validated, $name, $trip, $request) {
            $post = $thread->posts()->make([
                'number' => $thread->reply_count + 1,
                'name' => $name,
                'trip' => $trip,
                'body' => $validated['body'],
            ]);
            $post->ip_address = ContentModeration::clientIp($request);
            $post->ip_hash = ContentModeration::clientIpHash($request);
            $post->save();

            $thread->increment('reply_count');
            $thread->update(['last_posted_at' => now()]);
        });

        return redirect(route('threads.show', [$board, $thread]) . '#post-' . $thread->reply_count);
    }

    /**
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
