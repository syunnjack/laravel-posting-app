<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Thread;

class BoardController extends Controller
{
    public function index()
    {
        // 成人向けの板はトップの一覧に出さない。
        // 全年齢向けの板と並べると、年齢確認を経ずに辿れてしまう。
        $boards = Board::general()
            ->withCount('threads')
            ->with('latestThread')
            ->orderBy('position')
            ->get();

        // 年齢確認を通した人にだけ、一覧の下に入口を出す。
        $adultBoards = request()->session()->get('age_verified')
            ? Board::where('is_adult', true)->orderBy('position')->get()
            : collect();

        return view('boards.index', compact('boards', 'adultBoards'));
    }

    public function show(Board $board)
    {
        // 成人向けの板は、年齢確認を通していなければ確認画面へ送る。
        if ($board->is_adult && ! request()->session()->get('age_verified')) {
            return redirect()->route('age-check', ['to' => $board->slug]);
        }

        $query = $board->threads()->orderByDesc('last_posted_at');

        if ($q = request('q')) {
            $query->where('title', 'like', '%'.$q.'%');
        }

        $threads = $query->paginate(30)->withQueryString();

        return view('boards.show', compact('board', 'threads'));
    }

    public function sitemap()
    {
        // 成人向けの板とそのスレッドは sitemap に載せない。
        $boards = Board::general()->get(['id', 'slug', 'updated_at']);
        $threads = Thread::select('id', 'board_id', 'updated_at')
            ->whereHas('board', fn ($q) => $q->where('is_adult', false))
            ->with('board:id,slug')
            ->get();

        $xml = view('sitemap', compact('boards', 'threads'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
