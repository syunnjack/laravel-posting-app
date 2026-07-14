<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Report;
use App\Models\Thread;
use App\Support\ContentModeration;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function createRemovalRequest()
    {
        return view('reports.create');
    }

    public function storeRemovalRequest(Request $request)
    {
        if (! empty($request->input('website'))) {
            return redirect()->route('reports.remove-request')->with('success', '削除依頼を受け付けました。');
        }

        $validated = $request->validate([
            'target_url' => 'required|string|max:500',
            'reporter_email' => 'required|email|max:255',
            'reason' => 'required|string|min:5|max:1000',
        ]);

        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("removal_request:{$ipHash}", 60)) {
            return back()->withErrors(['reason' => '送信間隔が短すぎます。しばらく待ってから再度お試しください。'])->withInput();
        }

        [$threadId, $threadPostId] = $this->resolveTargetFromUrl($validated['target_url']);

        $report = new Report([
            'channel' => 'removal_request',
            'thread_id' => $threadId,
            'thread_post_id' => $threadPostId,
            'reporter_email' => $validated['reporter_email'],
            'reason' => $validated['target_url'] . "\n\n" . $validated['reason'],
        ]);
        $report->ip_hash = $ipHash;
        $report->save();

        return redirect()->route('reports.remove-request')->with('success', '削除依頼を受け付けました。内容を確認のうえ対応いたします。');
    }

    public function quickReport(Request $request, Board $board, Thread $thread)
    {
        if (! empty($request->input('website'))) {
            return back()->with('success', '通報を受け付けました。');
        }

        $validated = $request->validate([
            'thread_post_id' => 'nullable|integer|exists:thread_posts,id',
            'reason' => 'required|string|min:2|max:500',
        ]);

        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("report:{$ipHash}", 10)) {
            return back()->withErrors(['reason' => '送信間隔が短すぎます。しばらく待ってから再度お試しください。']);
        }

        $report = new Report([
            'channel' => 'report',
            'thread_id' => $thread->id,
            'thread_post_id' => $validated['thread_post_id'] ?? null,
            'reason' => $validated['reason'],
        ]);
        $report->ip_hash = $ipHash;
        $report->save();

        return back()->with('success', '通報を受け付けました。');
    }

    /**
     * 削除依頼フォームで貼り付けられたURLからスレッド/レスを特定する。
     *
     * @return array{0: ?int, 1: ?int}
     */
    private function resolveTargetFromUrl(string $url): array
    {
        if (preg_match('#/boards/[^/]+/threads/(\d+)#', $url, $m)) {
            $threadId = (int) $m[1];

            return [$threadId, null];
        }

        return [null, null];
    }
}
