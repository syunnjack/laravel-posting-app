@extends('layouts.plain')

@section('title', $board->name . ' 板 | ' . config('app.name'))
@section('description', $board->name . '板のスレッド一覧です。' . $board->description)

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => config('app.name'), 'item' => url('/')],
      ['@type' => 'ListItem', 'position' => 2, 'name' => $board->name, 'item' => route('boards.show', $board)],
  ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
{{-- 投稿が0件のときは itemListElement が空になる。空のItemListはGoogleに
     無効な項目として扱われるため、1件以上あるときだけ出力する。 --}}
@if ($threads->isNotEmpty())
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
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
@endif
@endpush

@section('content')
<div class="container my-4">
  <nav aria-label="breadcrumb" class="small mb-2">
    <a href="{{ route('boards.index') }}" class="text-decoration-none">{{ config('app.name') }}</a> &gt; {{ $board->name }}
  </nav>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <h1 class="h4 fw-bold mb-0">{{ $board->name }}</h1>
    <a href="{{ route('threads.create', $board) }}" class="btn btn-danger">➕ 新規スレッド</a>
  </div>
  <p class="text-muted small mb-3">{{ $board->description }}</p>

  {{-- 板内検索 --}}
  <form method="GET" class="mb-3 d-flex gap-2">
    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="スレッドを検索…" style="max-width:260px;">
    <button type="submit" class="btn btn-sm btn-outline-secondary">検索</button>
    @if(request('q'))<a href="{{ route('boards.show', $board) }}" class="btn btn-sm btn-outline-secondary">クリア</a>@endif
  </form>

  <div class="list-group">
    @forelse ($threads as $thread)
      @php $isNew = $thread->last_posted_at && $thread->last_posted_at->gt(now()->subHours(3)); @endphp
      <a href="{{ route('threads.show', [$board, $thread]) }}" class="list-group-item list-group-item-action">
        <div class="d-flex justify-content-between align-items-start">
          <span class="fw-semibold me-2">
            @if($isNew)<span class="badge bg-danger me-1" style="font-size:.65rem;">NEW</span>@endif
            {{ $thread->title }}
          </span>
          <span class="text-muted small text-nowrap">{{ $thread->reply_count }} レス</span>
        </div>
        <small class="text-muted">最終更新: {{ optional($thread->last_posted_at)->diffForHumans() }}</small>
      </a>
    @empty
      @if(request('q'))
        <p class="text-muted p-3 mb-0">「{{ request('q') }}」に一致するスレッドは見つかりませんでした。</p>
      @else
        <p class="text-muted p-3 mb-0">まだスレッドがありません。最初のスレッドを立ててみましょう。</p>
      @endif
    @endforelse
  </div>

  <div class="mt-4">
    {{ $threads->links('pagination::bootstrap-5') }}
  </div>
</div>
@endsection
