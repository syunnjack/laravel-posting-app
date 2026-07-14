@extends('layouts.plain')

@php
  $opPost = $thread->opPost ?? $posts->firstWhere('number', 1);
@endphp

@section('title', $thread->title . ' | ' . $board->name . '板 | ' . config('app.name'))
@section('description', $thread->title . '（' . $board->name . '板、' . $thread->reply_count . 'レス）' . mb_substr(optional($opPost)->body ?? '', 0, 60))

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => config('app.name'), 'item' => url('/')],
      ['@type' => 'ListItem', 'position' => 2, 'name' => $board->name, 'item' => route('boards.show', $board)],
      ['@type' => 'ListItem', 'position' => 3, 'name' => $thread->title, 'item' => route('threads.show', [$board, $thread])],
  ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode(array_filter([
  '@context' => 'https://schema.org',
  '@type' => 'DiscussionForumPosting',
  'headline' => $thread->title,
  'articleBody' => optional($opPost)->body,
  'datePublished' => optional($opPost)->created_at?->toAtomString(),
  'author' => ['@type' => 'Person', 'name' => optional($opPost)->displayNameWithTrip() ?? '名無しさん'],
  'interactionStatistic' => [
      '@type' => 'InteractionCounter',
      'interactionType' => 'https://schema.org/CommentAction',
      'userInteractionCount' => max($thread->reply_count - 1, 0),
  ],
  'comment' => $posts->filter(fn ($p) => $p->number !== 1)->values()->map(function ($post) {
      return [
          '@type' => 'Comment',
          'text' => $post->body,
          'datePublished' => $post->created_at->toAtomString(),
          'author' => ['@type' => 'Person', 'name' => $post->displayNameWithTrip()],
      ];
  })->all(),
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="container my-4">
  <nav aria-label="breadcrumb" class="small mb-2">
    <a href="{{ route('boards.index') }}" class="text-decoration-none">{{ config('app.name') }}</a> &gt;
    <a href="{{ route('boards.show', $board) }}" class="text-decoration-none">{{ $board->name }}</a> &gt; {{ $thread->title }}
  </nav>

  <div class="d-flex justify-content-between align-items-start mb-3">
    <h1 class="h4 fw-bold mb-0">{{ $thread->title }}</h1>
    @if ($thread->is_locked)
      <span class="badge bg-secondary">ロック中</span>
    @endif
  </div>

  @if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger py-2 small">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  @foreach ($posts as $post)
    <div class="card mb-2" id="post-{{ $post->number }}">
      <div class="card-body py-2">
        <div class="d-flex justify-content-between small text-muted mb-1">
          <span>
            <strong class="text-dark">{{ $post->number }}</strong>
            {{ $post->displayNameWithTrip() }}
          </span>
          <span>{{ $post->created_at->format('Y/m/d H:i:s') }}</span>
        </div>
        <p class="post-body mb-2">{{ $post->body }}</p>
        <form method="POST" action="{{ route('reports.quick', [$board, $thread]) }}" class="d-inline">
          @csrf
          <input type="hidden" name="thread_post_id" value="{{ $post->id }}">
          <input type="hidden" name="reason" value="不適切な投稿の可能性">
          <button type="submit" class="btn btn-link btn-sm text-muted p-0" onclick="return confirm('このレスを通報しますか？');">🚩 通報</button>
        </form>
        @auth
          @if (auth()->user()->is_admin)
            <form method="POST" action="{{ route('moderation.thread-posts.destroy', $post) }}" class="d-inline ms-2">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-link btn-sm text-danger p-0" onclick="return confirm('このレスを削除しますか？');">🗑 削除</button>
            </form>
          @endif
        @endauth
      </div>
    </div>
  @endforeach

  <div class="my-3">
    {{ $posts->links('pagination::bootstrap-5') }}
  </div>

  @if ($thread->is_locked)
    <div class="alert alert-secondary">このスレッドはロックされているため、新しい返信はできません。</div>
  @else
    <h2 class="h6 mt-4 mb-2">返信する</h2>
    <form method="POST" action="{{ route('thread-posts.store', [$board, $thread]) }}" class="bg-light p-3 rounded shadow-sm">
      @csrf

      {{-- ハニーポット: 人間には見えない項目。ボットが埋めた場合は投稿を無視する --}}
      <div style="position:absolute; left:-9999px;" aria-hidden="true">
        <label>ウェブサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
      </div>

      <div class="mb-2">
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" maxlength="64" placeholder="名前（省略可、トリップ可）">
      </div>
      <textarea name="body" rows="3" class="form-control mb-2" maxlength="2000" required minlength="1" placeholder="レスを入力してください">{{ old('body') }}</textarea>
      <button type="submit" class="btn btn-dark">レスを送信</button>
    </form>
  @endif
</div>
@endsection
