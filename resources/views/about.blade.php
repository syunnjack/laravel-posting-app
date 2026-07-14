@extends('layouts.plain')

@section('title', 'このサイトについて | ' . config('app.name'))
@section('description', config('app.name') . 'の運営方針、投稿の取り扱い、モデレーションについて説明しています。')

@section('content')
<div class="container my-4" style="max-width: 720px;">
  <h1 class="h4 fw-bold mb-4">このサイトについて</h1>

  <section class="mb-4">
    <h2 class="h6">サイトの目的</h2>
    <p class="text-muted small">
      「{{ config('app.name') }}」は、ログイン不要・匿名で誰でもスレッドを立てたり返信したりできる掲示板です。
      雑談・ニュース・趣味など複数の板を用意しており、話題ごとに自由に投稿できます。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">投稿について</h2>
    <p class="text-muted small">
      投稿はすべて匿名で行われ、名前欄を空欄にした場合は「名無しさん」として表示されます。
      名前欄に「名前#合言葉」の形式で入力すると、同じ合言葉を使い続ける限り同一の識別子（トリップ）が表示されます。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">モデレーションについて</h2>
    <p class="text-muted small">
      投稿内容には自動のNGワードフィルタを適用していますが、これはあくまで一次的な機械的チェックであり、
      名誉毀損や個人情報の暴露などを完全に自動検出できるものではありません。実際の削除対応は、
      利用者からの通報・削除依頼を運営者が確認したうえで行っています。詳しい禁止事項は<a href="{{ route('rules') }}">利用規約</a>をご覧ください。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">投稿の削除・情報開示について</h2>
    <p class="text-muted small">
      問題のある投稿を見つけた場合は<a href="{{ route('reports.remove-request') }}">削除依頼フォーム</a>からご連絡ください。
      また、投稿時のIPアドレスはサーバー内に記録しており、正当な法的手続きによる開示請求があった場合には対応することがあります。
      詳細は<a href="{{ route('rules') }}">利用規約</a>をご覧ください。
    </p>
  </section>

  <a href="{{ route('boards.index') }}" class="d-block text-center text-muted mt-4">トップページに戻る</a>
</div>
@endsection
