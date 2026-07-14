@extends('layouts.plain')

@section('title', $board->name . ' 板 | ' . config('app.name'))
@section('description', $board->name . '板のスレッド一覧です。' . $board->description)

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => config('app.name'), 'item' => url('/')],
      ['@type' => 'ListItem', 'position' => 2, 'name' => $board->name, 'item' => route('boards.show', $board)],
  ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'ItemList',
  'itemListElement' => $threads->values()->map(function ($thread, $i) use ($board) {
      return [
          '@type' => 'ListItem',
          'position' => $i + 1,
          'url' => route('threads.show', [$board, $thread]),
          'name' => $thread->title,
      ];
  })->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="container my-4">
  <nav aria-label="breadcrumb" class="small mb-2">
    <a href="{{ route('boards.index') }}" class="text-decoration-none">{{ config('app.name') }}</a> &gt; {{ $board->name }}
  </nav>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 fw-bold mb-0">{{ $board->name }}</h1>
    <a href="{{ route('threads.create', $board) }}" class="btn btn-danger">➕ 新規スレッド作成</a>
  </div>
  <p class="text-muted small">{{ $board->description }}</p>

  <div class="list-group">
    @forelse ($threads as $thread)
      <a href="{{ route('threads.show', [$board, $thread]) }}" class="list-group-item list-group-item-action">
        <div class="d-flex justify-content-between">
          <span class="fw-semibold">{{ $thread->title }}</span>
          <span class="text-muted small">{{ $thread->reply_count }} レス</span>
        </div>
        <small class="text-muted">最終更新: {{ optional($thread->last_posted_at)->diffForHumans() }}</small>
      </a>
    @empty
      <p class="text-muted">まだスレッドがありません。最初のスレッドを立ててみましょう。</p>
    @endforelse
  </div>

  <div class="mt-4">
    {{ $threads->links('pagination::bootstrap-5') }}
  </div>
</div>
@endsection
