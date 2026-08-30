@extends('layouts.plain')

@section('title', '年齢確認 | ' . config('app.name'))
@section('description', '成人向けの板をご覧いただく前の年齢確認ページです。')

@section('content')
<div class="container my-5" style="max-width: 560px;">
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5 text-center">
      <span class="badge bg-danger mb-3">18+</span>
      <h1 class="h4 fw-bold mb-3">年齢確認</h1>

      <p class="text-muted small mb-4">
        @if ($board)
          「{{ $board->name }}」は成人向けの板です。
        @else
          この先は成人向けの板です。
        @endif
        18歳未満の方はご利用いただけません。
      </p>

      <form method="POST" action="{{ route('age-check.confirm') }}">
        @csrf
        <input type="hidden" name="to" value="{{ request('to') }}">
        <button type="submit" class="btn btn-danger w-100 mb-2">18歳以上です</button>
      </form>

      <a href="{{ route('boards.index') }}" class="btn btn-outline-secondary w-100">
        戻る
      </a>

      <p class="text-muted mt-4 mb-0" style="font-size: .75rem;">
        確認の結果はブラウザを閉じるまで保持されます。
        年齢を記録したり、サーバーに送信したりはしていません。
      </p>
    </div>
  </div>
</div>
@endsection
