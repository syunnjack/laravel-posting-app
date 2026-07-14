@extends('layouts.plain')

@section('title', '利用規約 | ' . config('app.name'))
@section('description', config('app.name') . 'の利用規約です。禁止事項とIPアドレスの記録・開示に関する方針を掲載しています。')

@section('content')
<div class="container my-4" style="max-width: 720px;">
  <h1 class="h4 fw-bold mb-4">利用規約</h1>

  <section class="mb-4">
    <h2 class="h6">禁止事項</h2>
    <p class="text-muted small">
      本サイトへの投稿にあたり、以下の行為を禁止します。
    </p>
    <ul class="text-muted small">
      <li>特定の個人・団体に対する名誉毀損、誹謗中傷</li>
      <li>他人の氏名・住所・電話番号・勤務先など個人を特定できる情報の暴露</li>
      <li>法令に違反する内容の投稿、違法行為を助長・勧誘する内容</li>
      <li>スパム行為、広告目的の連続投稿、自動投稿ツールの使用</li>
      <li>その他、公序良俗に反する投稿</li>
    </ul>
  </section>

  <section class="mb-4">
    <h2 class="h6">IPアドレスの記録・開示について</h2>
    <p class="text-muted small">
      本サイトは、投稿時のIPアドレスをサーバー内に記録しています。これは公開の場に表示されることはありませんが、
      プロバイダ責任制限法（情報流通プラットフォーム対処法）に基づく発信者情報開示請求など、正当な法的手続きに基づく開示要請があった場合には、
      当該記録を裁判所または法令の定める手続きに従って開示することがあります。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">投稿の削除について</h2>
    <p class="text-muted small">
      禁止事項に該当する投稿を発見した場合は、各レスの「通報」ボタン、または<a href="{{ route('reports.remove-request') }}">削除依頼フォーム</a>からご連絡ください。
      内容を確認のうえ、削除等の対応を行います。
    </p>
  </section>

  <a href="{{ route('boards.index') }}" class="d-block text-center text-muted mt-4">トップページに戻る</a>
</div>
@endsection
