@extends('layouts.plain')

@section('title', '削除依頼 | ' . config('app.name'))
@section('description', config('app.name') . 'への削除依頼フォームです。名誉毀損・個人情報の暴露・違法な投稿等を発見した場合はこちらからご連絡ください。')

@section('content')
<div class="container my-4" style="max-width: 640px;">
  <h1 class="h4 fw-bold mb-3">削除依頼</h1>
  <p class="text-muted small">
    名誉毀損・個人情報の暴露・違法なコンテンツ等、問題のある投稿を発見された場合は、対象のURLと理由をご記入のうえ送信してください。
    内容を確認のうえ対応いたします。返信が必要な場合はメールアドレス宛にご連絡します。
  </p>

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

  <form method="POST" action="{{ route('reports.remove-request.store') }}" class="bg-light p-3 rounded shadow-sm">
    @csrf

    {{-- ハニーポット --}}
    <div style="position:absolute; left:-9999px;" aria-hidden="true">
      <label>ウェブサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div class="mb-3">
      <label class="form-label">対象のスレッドURL <span class="text-danger">*</span></label>
      <input type="text" name="target_url" value="{{ old('target_url') }}" class="form-control" required placeholder="{{ url('/boards/zatsudan/threads/1') }}">
    </div>

    <div class="mb-3">
      <label class="form-label">連絡先メールアドレス <span class="text-danger">*</span></label>
      <input type="email" name="reporter_email" value="{{ old('reporter_email') }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">削除を希望する理由 <span class="text-danger">*</span></label>
      <textarea name="reason" rows="5" class="form-control" required minlength="5" maxlength="1000">{{ old('reason') }}</textarea>
    </div>

    <button type="submit" class="btn btn-danger w-100">削除依頼を送信する</button>
  </form>
</div>
@endsection
