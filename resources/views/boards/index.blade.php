@extends('layouts.plain')

@section('title', config('app.name') . ' | 誰でも匿名で書き込める掲示板')
@section('description', config('app.name') . 'のトップページ。雑談・ニュース・趣味など全' . $boards->count() . '板から好きな話題を選んでスレッドを立てたり返信したりできます。')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@type' => 'WebSite',
  'name' => config('app.name'),
  'url' => url('/'),
  'description' => '誰でも匿名でスレッドを立てたり返信したりできる無料の掲示板。',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
{{-- 投稿が0件のときは itemListElement が空になる。空のItemListはGoogleに
     無効な項目として扱われるため、1件以上あるときだけ出力する。 --}}
@if ($boards->isNotEmpty())
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@type' => 'ItemList',
  'itemListElement' => $boards->values()->map(function ($board, $i) {
      return [
          '@type' => 'ListItem',
          'position' => $i + 1,
          'url' => route('boards.show', $board),
          'name' => $board->name,
      ];
  })->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush

@section('content')
<div class="container my-4">
  <h1 class="h3 fw-bold text-center mb-1">📝 {{ config('app.name') }}</h1>
  <p class="text-muted text-center mb-4">誰でも匿名で書き込める掲示板。<strong>{{ $boards->count() }}</strong>板を用意しています。</p>

  {{-- サイト全体検索へのリンク --}}
  <div class="text-center mb-4">
    <a href="{{ route('rules') }}" class="btn btn-sm btn-outline-secondary me-2">📋 利用ルール</a>
    <a href="{{ route('about') }}" class="btn btn-sm btn-outline-secondary">ℹ サイトについて</a>
  </div>

  <div class="row g-3">
    @foreach ($boards as $board)
      @php $latestThread = $board->latestThread; @endphp
      <div class="col-md-6">
        <a href="{{ route('boards.show', $board) }}" class="card text-decoration-none shadow-sm h-100 border-0">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <h2 class="h5 card-title text-dark mb-1">{{ $board->name }}</h2>
              <span class="badge bg-light text-secondary border">{{ $board->threads_count }} スレ</span>
            </div>
            <p class="card-text text-muted small mb-2">{{ $board->description }}</p>
            @if($latestThread)
              <p class="mb-0 small text-muted">
                🕐 <span class="text-dark">{{ Str::limit($latestThread->title, 28) }}</span>
                <span class="ms-1">{{ optional($latestThread->last_posted_at)->diffForHumans() }}</span>
              </p>
            @else
              <p class="mb-0 small text-muted">まだスレッドがありません。最初の1つを立ててみてください。</p>
            @endif
          </div>
        </a>
      </div>
    @endforeach
  </div>

  {{-- 成人向けの板。年齢確認を通した人にだけ出す。
       未確認の場合はここに何も表示せず、確認への導線だけを置く。 --}}
  @if (isset($adultBoards) && $adultBoards->isNotEmpty())
    <hr class="my-4">
    <h2 class="h6 text-muted mb-3">成人向け <span class="badge bg-danger align-middle">18+</span></h2>
    <div class="row g-3">
      @foreach ($adultBoards as $adultBoard)
        <div class="col-md-6">
          <a href="{{ route('boards.show', $adultBoard) }}" class="card text-decoration-none shadow-sm h-100 border-0">
            <div class="card-body">
              <h3 class="h5 card-title text-dark mb-1">{{ $adultBoard->name }}</h3>
              <p class="card-text text-muted small mb-0">{{ $adultBoard->description }}</p>
            </div>
          </a>
        </div>
      @endforeach
    </div>
  @else
    <hr class="my-4">
    <p class="text-muted small mb-0">
      成人向けの板もあります。ご覧になるには
      <a href="{{ route('age-check') }}">年齢確認</a>が必要です（18歳未満の方はご利用いただけません）。
    </p>
  @endif
</div>
@endsection
