<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Thread;
use App\Models\ThreadPost;
use App\Support\ContentModeration;
use App\Support\PostName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThreadController extends Controller
{
    public function create(Request $request, Board $board)
    {
        // 成人向けの板は、年齢確認を通していなければ入れない。
        if ($board->is_adult && ! $request->session()->get('age_verified')) {
            return redirect()->route('age-check', ['to' => $board->slug]);
        }

        return view('threads.create', compact('board'));
    }

    public function store(Request $request, Board $board)
    {
        // 成人向けの板は、年齢確認を通していなければ入れない。
        if ($board->is_adult && ! $request->session()->get('age_verified')) {
            return redirect()->route('age-check', ['to' => $board->slug]);
        }

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

        if (ContentModeration::isLinkSpam($validated['title'] . ' ' . $validated['body'])) {
            return back()->withErrors(['body' => 'リンクは1つの投稿につき'.ContentModeration::maxLinks().'つまでです。URLだけの投稿もご遠慮ください。'])->withInput();
        }

        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("thread_create:{$ipHash}", 45)) {
            return back()->withErrors(['body' => '投稿間隔が短すぎます。しばらく待ってから再度お試しください。'])->withInput();
        }

        [$name, $trip] = PostName::parse($validated['name'] ?? '');

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
        // URL を直接開かれても、年齢確認を通していなければ読めないようにする。
        if ($board->is_adult && ! request()->session()->get('age_verified')) {
            return redirect()->route('age-check', ['to' => $board->slug]);
        }

        $posts = $thread->posts()->paginate(50);

        return view('threads.show', compact('board', 'thread', 'posts'));
    }
}
