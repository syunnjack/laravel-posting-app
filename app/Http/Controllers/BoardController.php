<?php

namespace App\Http\Controllers;

use App\Models\Board;

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::withCount('threads')->orderBy('position')->get();

        return view('boards.index', compact('boards'));
    }

    public function show(Board $board)
    {
        $threads = $board->threads()
            ->orderByDesc('last_posted_at')
            ->paginate(20);

        return view('boards.show', compact('board', 'threads'));
    }

    public function sitemap()
    {
        $boards = Board::all(['id', 'slug', 'updated_at']);
        $threads = \App\Models\Thread::select('id', 'board_id', 'updated_at')
            ->with('board:id,slug')
            ->get();

        $xml = view('sitemap', compact('boards', 'threads'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
