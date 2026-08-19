<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="google-site-verification" content="GXBCQChIpk5EECrci5xyqqDdVZlLuTYDVBaT13e-bY4" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#212529">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', config('app.name') . ' | 匿名掲示板')</title>
  <meta name="description" content="@yield('description', config('app.name') . 'は、誰でも匿名でスレッドを立てたり返信したりできる無料の掲示板です。雑談・ニュース・趣味など幅広い話題の板を用意しています。')">
  @php
      // url()->current() はクエリを落とすため、2ページ目以降が1ページ目を
      // 正規URLとして申告してしまう。内容が変わる page だけを残す。
      $canonicalQuery = array_filter(request()->only(['page']), fn ($value) => $value !== null && $value !== '' && $value !== '1');
      $canonicalUrl = url()->current() . ($canonicalQuery ? '?' . http_build_query($canonicalQuery) : '');

      // 板内検索の結果と、投稿フォームなどの操作用ページは検索結果に出す意味が無い。
      // リンクはたどってほしいので follow は残す。
      $noindexRoutes = ['threads.create', 'reports.remove-request'];
      $isNoindex = request()->filled('q') || in_array(request()->route()?->getName(), $noindexRoutes, true);
  @endphp
  @if ($isNoindex)
  <meta name="robots" content="noindex,follow">
  @endif
  <link rel="canonical" href="{{ $canonicalUrl }}">

  <meta property="og:site_name" content="{{ config('app.name') }}">
  <meta property="og:type" content="website">
  <meta property="og:title" content="@yield('title', config('app.name') . ' | 匿名掲示板')">
  <meta property="og:description" content="@yield('description', config('app.name') . 'は、誰でも匿名でスレッドを立てたり返信したりできる無料の掲示板です。雑談・ニュース・趣味など幅広い話題の板を用意しています。')">
  <meta property="og:url" content="{{ $canonicalUrl }}">
  <meta property="og:locale" content="ja_JP">

  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="@yield('title', config('app.name') . ' | 匿名掲示板')">
  <meta name="twitter:description" content="@yield('description', config('app.name') . 'は、誰でも匿名でスレッドを立てたり返信したりできる無料の掲示板です。雑談・ニュース・趣味など幅広い話題の板を用意しています。')">

  <link rel="icon" href="/favicon.ico" sizes="any">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: system-ui, -apple-system, sans-serif;
    }
    .btn { min-height: 44px; }
    .post-body { white-space: pre-wrap; word-break: break-word; }
  </style>
  @yield('styles')

  @stack('structured-data')
  @if(config('services.ga4.id'))
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.ga4.id') }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('services.ga4.id') }}');
  </script>
  @endif
</head>
<body>
  <nav class="navbar navbar-dark bg-dark p-2">
    <div class="container-fluid">
      <a href="{{ route('boards.index') }}" class="navbar-brand text-white text-decoration-none">📝 {{ config('app.name') }}</a>
      <div class="d-flex gap-3 align-items-center">
        @auth
          <a href="{{ route('moderation.index') }}" class="text-white small text-decoration-none">モデレーション</a>
        @endauth
        <a href="{{ route('about') }}" class="text-white small text-decoration-none">サイトについて</a>
      </div>
    </div>
  </nav>

  @yield('content')

  <footer class="text-center text-muted small py-4 mt-4">
    <a href="{{ route('rules') }}" class="text-muted me-3">利用規約</a>
    <a href="{{ route('reports.remove-request') }}" class="text-muted me-3">削除依頼</a>
    <a href="{{ route('about') }}" class="text-muted">このサイトについて</a>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @yield('scripts')
</body>
</html>
