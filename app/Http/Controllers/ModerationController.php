<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Thread;
use App\Models\ThreadPost;

class ModerationController extends Controller
{
    public function index()
    {
        $pendingReports = Report::with(['thread.board', 'threadPost'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $recentThreads = Thread::with('board')
            ->latest('last_posted_at')
            ->take(30)
            ->get();

        return view('moderation.index', compact('pendingReports', 'recentThreads'));
    }

    public function lockThread(Thread $thread)
    {
        $thread->update(['is_locked' => ! $thread->is_locked]);

        return back()->with('success', $thread->is_locked ? 'スレッドをロックしました。' : 'スレッドのロックを解除しました。');
    }

    public function destroyThread(Thread $thread)
    {
        $thread->delete();

        return back()->with('success', 'スレッドを削除しました。');
    }

    public function destroyPost(ThreadPost $threadPost)
    {
        $thread = $threadPost->thread;
        $threadPost->delete();

        if ($thread && $thread->reply_count > 0) {
            $thread->decrement('reply_count');
        }

        return back()->with('success', 'レスを削除しました。');
    }

    public function resolveReport(Report $report)
    {
        // status/resolved_atは通報フォームからの入力ではなく運営操作専用のため
        // 意図的に$fillable対象外にしている。ここでは直接代入する。
        $report->status = 'resolved';
        $report->resolved_at = now();
        $report->save();

        return back()->with('success', '通報を解決済みにしました。');
    }
}
