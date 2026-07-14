@extends('layouts.plain')

@section('title', $board->name . '板に新規スレッドを立てる | ' . config('app.name'))
@section('description', $board->name . '板に新しいスレッドを立てます。ログイン不要・匿名で投稿できます。')

@section('content')
<div class="container my-4" style="max-width: 640px;">
  <nav aria-label="breadcrumb" class="small mb-2">
    <a href="{{ route('boards.index') }}" class="text-decoration-none">{{ config('app.name') }}</a> &gt;
    <a href="{{ route('boards.show', $board) }}" class="text-decoration-none">{{ $board->name }}</a> &gt; 新規スレッド作成
  </nav>

  <h1 class="h4 fw-bold mb-3">➕ {{ $board->name }}板に新規スレッドを立てる</h1>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('threads.store', $board) }}" class="bg-light p-3 rounded shadow-sm">
    @csrf

    {{-- ハニーポット: 人間には見えない項目。ボットが埋めた場合は投稿を無視する --}}
    <div style="position:absolute; left:-9999px;" aria-hidden="true">
      <label>ウェブサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div class="mb-3">
      <label class="form-label">名前（トリップ可、例: 名無し#秘密の合言葉）</label>
      <input type="text" name="name" value="{{ old('name') }}" class="form-control" maxlength="64" placeholder="無記名の場合は「名無しさん」になります">
    </div>

    <div class="mb-3">
      <label class="form-label">スレッドタイトル <span class="text-danger">*</span></label>
      <input type="text" name="title" value="{{ old('title') }}" class="form-control" maxlength="100" required>
    </div>

    <div class="mb-3">
      <label class="form-label">本文 <span class="text-danger">*</span></label>
      <textarea name="body" rows="6" class="form-control" maxlength="2000" required minlength="2">{{ old('body') }}</textarea>
    </div>

    <button type="submit" class="btn btn-danger w-100">スレッドを立てる</button>
  </form>
</div>
@endsection
