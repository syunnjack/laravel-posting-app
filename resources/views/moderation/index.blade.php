@extends('layouts.plain')

@section('title', 'モデレーション | ' . config('app.name'))

@section('content')
<div class="container my-4">
  <h1 class="h4 fw-bold mb-4">モデレーション</h1>

  @if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
  @endif

  <h2 class="h6 mb-2">未対応の通報・削除依頼（{{ $pendingReports->count() }}件）</h2>
  <div class="table-responsive mb-4">
    <table class="table table-sm bg-white">
      <thead>
        <tr>
          <th>種別</th>
          <th>対象</th>
          <th>理由</th>
          <th>連絡先</th>
          <th>受付日時</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($pendingReports as $report)
          <tr>
            <td>{{ $report->channel === 'removal_request' ? '削除依頼' : '通報' }}</td>
            <td>
              @if ($report->thread)
                <a href="{{ route('threads.show', [$report->thread->board, $report->thread]) }}" target="_blank">{{ $report->thread->title }}</a>
              @else
                (URL記載)
              @endif
            </td>
            <td style="max-width: 300px; white-space: pre-wrap;">{{ $report->reason }}</td>
            <td>{{ $report->reporter_email }}</td>
            <td>{{ $report->created_at->format('Y/m/d H:i') }}</td>
            <td>
              <form method="POST" action="{{ route('moderation.reports.resolve', $report) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-outline-secondary">解決済みにする</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-muted">未対応の通報・削除依頼はありません。</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <h2 class="h6 mb-2">最近のスレッド</h2>
  <div class="table-responsive">
    <table class="table table-sm bg-white">
      <thead>
        <tr>
          <th>板</th>
          <th>タイトル</th>
          <th>レス数</th>
          <th>状態</th>
          <th>最終更新</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($recentThreads as $thread)
          <tr>
            <td>{{ $thread->board->name }}</td>
            <td><a href="{{ route('threads.show', [$thread->board, $thread]) }}" target="_blank">{{ $thread->title }}</a></td>
            <td>{{ $thread->reply_count }}</td>
            <td>{{ $thread->is_locked ? 'ロック中' : '通常' }}</td>
            <td>{{ optional($thread->last_posted_at)->format('Y/m/d H:i') }}</td>
            <td class="d-flex gap-1">
              <form method="POST" action="{{ route('moderation.threads.lock', $thread) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $thread->is_locked ? 'ロック解除' : 'ロック' }}</button>
              </form>
              <form method="POST" action="{{ route('moderation.threads.destroy', $thread) }}" onsubmit="return confirm('このスレッドを削除しますか？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">削除</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
