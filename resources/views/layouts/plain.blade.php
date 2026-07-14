<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#212529">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', config('app.name') . ' | 匿名掲示板')</title>
  <meta name="description" content="@yield('description', config('app.name') . 'は、誰でも匿名でスレッドを立てたり返信したりできる無料の掲示板です。雑談・ニュース・趣味など幅広い話題の板を用意しています。')">
  <link rel="canonical" href="{{ url()->current() }}">

  <meta property="og:site_name" content="{{ config('app.name') }}">
  <meta property="og:type" content="website">
  <meta property="og:title" content="@yield('title', config('app.name') . ' | 匿名掲示板')">
  <meta property="og:description" content="@yield('description', config('app.name') . 'は、誰でも匿名でスレッドを立てたり返信したりできる無料の掲示板です。雑談・ニュース・趣味など幅広い話題の板を用意しています。')">
  <meta property="og:url" content="{{ url()->current() }}">
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
