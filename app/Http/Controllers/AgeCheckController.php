<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;

class AgeCheckController extends Controller
{
    /** 年齢確認の画面。どの板へ戻すかを持ち回る。 */
    public function show(Request $request)
    {
        $to = $request->query('to');
        $board = $to ? Board::where('slug', $to)->where('is_adult', true)->first() : null;

        return view('age-check', ['board' => $board]);
    }

    /** 「18歳以上です」を押したときの受け口。セッションにだけ記録する。 */
    public function confirm(Request $request)
    {
        $request->session()->put('age_verified', true);

        $to = $request->input('to');
        if ($to && Board::where('slug', $to)->where('is_adult', true)->exists()) {
            return redirect()->route('boards.show', $to);
        }

        return redirect()->route('boards.index');
    }
}
