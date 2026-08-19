@extends('layouts.plain')

@php
  $opPost = $thread->opPost ?? $posts->firstWhere('number', 1);
@endphp

@section('title', $thread->title . ' | ' . $board->name . '板 | ' . config('app.name'))
@section('description', $thread->title . '（' . $board->name . '板、' . $thread->reply_count . 'レス）' . mb_substr(optional($opPost)->body ?? '', 0, 60))

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
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
  '@@context' => 'https://schema.org',
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
    <div class="d-flex gap-2 align-items-center">
      @if ($thread->is_locked)
        <span class="badge bg-secondary">ロック中</span>
      @endif
      <button class="btn btn-sm btn-outline-secondary" onclick="copyUrl()" title="URLをコピー">🔗 URL</button>
      <a href="#reply-form" class="btn btn-sm btn-outline-primary" id="scroll-new">⬇ 最新レス</a>
    </div>
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
            <strong class="text-dark post-anchor" style="cursor:pointer;" onclick="quotePost({{ $post->number }})" title="クリックで引用">{{ $post->number }}</strong>
            {{ $post->displayNameWithTrip() }}
          </span>
          <span>{{ $post->created_at->format('Y/m/d H:i:s') }}</span>
        </div>
        <p class="post-body mb-2" data-body="{{ e($post->body) }}">{{ $post->body }}</p>
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
    <h2 class="h6 mt-4 mb-2" id="reply-form">返信する <small class="text-muted fw-normal">（番号クリックで引用）</small></h2>
    <form method="POST" action="{{ route('thread-posts.store', [$board, $thread]) }}" class="bg-light p-3 rounded shadow-sm" id="reply-form-el">
      @csrf
      <div style="position:absolute; left:-9999px;" aria-hidden="true">
        <label>ウェブサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
      </div>
      <div class="row g-2 mb-2">
        <div class="col-sm-6">
          <input type="text" name="name" id="post-name" value="{{ old('name') }}" class="form-control" maxlength="64" placeholder="名前（省略可、#トリップ可）">
        </div>
        <div class="col-sm-6 d-flex align-items-center gap-2">
          <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" name="sage" id="post-sage" value="1">
            <label class="form-check-label small" for="post-sage">sage（スレを上げない）</label>
          </div>
        </div>
      </div>
      <textarea name="body" id="post-body" rows="4" class="form-control mb-2" maxlength="2000" required minlength="1" placeholder="レスを入力（Ctrl+Enterで送信）">{{ old('body') }}</textarea>
      <div id="preview-area" class="border rounded bg-white p-2 mb-2 small post-body" style="display:none;min-height:40px;white-space:pre-wrap;word-break:break-word;"></div>
      <p class="text-muted small mb-2">リンクは1つの投稿につき2つまで貼れます。URLだけの投稿はできません。</p>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-dark">送信</button>
        <button type="button" class="btn btn-outline-secondary" onclick="togglePreview()">プレビュー</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearDraft()">クリア</button>
      </div>
    </form>
  @endif
</div>

@section('styles')
<style>
  .post-anchor:hover { color: #0d6efd !important; text-decoration: underline; }
  .post-anchor { user-select: none; }
  .post-body a { color: #0d6efd; }
  .quote-ref { color: #198754; font-weight: bold; cursor: pointer; }
  .quote-ref:hover { text-decoration: underline; }
  #reply-form-el { scroll-margin-top: 70px; }
</style>
@endsection

@section('scripts')
<script>
// 投稿本文の >>N リンクとURL自動リンク処理
document.querySelectorAll('.post-body[data-body]').forEach(el => {
  let html = el.dataset.body
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/>>(\d+)/g, (_, n) => `<a href="#post-${n}" class="quote-ref">&gt;&gt;${n}</a>`)
    .replace(/(https?:\/\/[^\s"<>（）【】]+)/g, url => `<a href="${url}" target="_blank" rel="ugc nofollow noopener noreferrer">${url}</a>`);
  el.innerHTML = html;
});

// 名前をlocalStorageに記憶
const nameInput = document.getElementById('post-name');
const sageCheck = document.getElementById('post-sage');
if (nameInput) {
  nameInput.value = nameInput.value || localStorage.getItem('bbs_name') || '';
  nameInput.addEventListener('blur', () => localStorage.setItem('bbs_name', nameInput.value));
}
if (sageCheck) {
  sageCheck.checked = localStorage.getItem('bbs_sage') === '1';
  sageCheck.addEventListener('change', () => localStorage.setItem('bbs_sage', sageCheck.checked ? '1' : '0'));
}

// 番号クリックで引用
function quotePost(n) {
  const body = document.getElementById('post-body');
  if (!body) return;
  const quote = `>>${n}\n`;
  body.value = body.value ? body.value + '\n' + quote : quote;
  body.focus();
  body.setSelectionRange(body.value.length, body.value.length);
  document.getElementById('reply-form-el')?.scrollIntoView({ behavior: 'smooth' });
}

// プレビュー
function togglePreview() {
  const area = document.getElementById('preview-area');
  const body = document.getElementById('post-body');
  if (area.style.display === 'none') {
    let html = body.value
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/>>(\d+)/g, (_, n) => `<a href="#post-${n}" class="quote-ref">&gt;&gt;${n}</a>`)
      .replace(/(https?:\/\/[^\s"<>（）【】]+)/g, url => `<a href="${url}" target="_blank" rel="ugc nofollow noopener noreferrer">${url}</a>`);
    area.innerHTML = html || '<span class="text-muted">（本文を入力してください）</span>';
    area.style.display = 'block';
  } else {
    area.style.display = 'none';
  }
}

// Ctrl+Enter で送信
document.getElementById('post-body')?.addEventListener('keydown', e => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
    e.preventDefault();
    document.getElementById('reply-form-el')?.submit();
  }
});

// 下書きクリア
function clearDraft() {
  if (confirm('入力中の内容を消去しますか？')) {
    document.getElementById('post-body').value = '';
    document.getElementById('preview-area').style.display = 'none';
  }
}

// URL コピー
function copyUrl() {
  navigator.clipboard?.writeText(location.href)
    .then(() => { const b = document.querySelector('[onclick="copyUrl()"]'); if(b){ b.textContent='✅ コピー完了'; setTimeout(()=>b.textContent='🔗 URL',2000); }});
}

// j/k キーボードナビゲーション
let currentPost = 0;
const posts = [...document.querySelectorAll('[id^="post-"]')];
document.addEventListener('keydown', e => {
  if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') return;
  if (e.key === 'j' && currentPost < posts.length - 1) {
    posts[++currentPost].scrollIntoView({ behavior: 'smooth', block: 'center' });
  } else if (e.key === 'k' && currentPost > 0) {
    posts[--currentPost].scrollIntoView({ behavior: 'smooth', block: 'center' });
  } else if (e.key === 'r') {
    document.getElementById('reply-form-el')?.scrollIntoView({ behavior: 'smooth' });
    document.getElementById('post-body')?.focus();
  }
});
</script>
@endsection

