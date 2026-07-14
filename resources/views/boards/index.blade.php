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
@endpush

@section('content')
<div class="container my-4">
  <h1 class="h3 fw-bold text-center mb-2">📝 {{ config('app.name') }}</h1>
  <p class="text-muted text-center mb-4">誰でも匿名でスレッドを立てたり、返信したりできる掲示板です。</p>

  <div class="row g-3">
    @foreach ($boards as $board)
      <div class="col-md-6">
        <a href="{{ route('boards.show', $board) }}" class="card text-decoration-none shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 card-title text-dark mb-1">{{ $board->name }}</h2>
            <p class="card-text text-muted small mb-1">{{ $board->description }}</p>
            <span class="badge bg-secondary">{{ $board->threads_count }} スレッド</span>
          </div>
        </a>
      </div>
    @endforeach
  </div>
</div>
@endsection
