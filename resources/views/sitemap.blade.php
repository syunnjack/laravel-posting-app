<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>{{ url('/') }}</loc>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>{{ url('/about') }}</loc>
    <priority>0.3</priority>
  </url>
  <url>
    <loc>{{ url('/rules') }}</loc>
    <priority>0.3</priority>
  </url>
@foreach ($boards as $board)
  <url>
    <loc>{{ url("/boards/{$board->slug}") }}</loc>
    <lastmod>{{ $board->updated_at->toAtomString() }}</lastmod>
    <priority>0.8</priority>
  </url>
@endforeach
@foreach ($threads as $thread)
  <url>
    <loc>{{ url("/boards/{$thread->board->slug}/threads/{$thread->id}") }}</loc>
    <lastmod>{{ $thread->updated_at->toAtomString() }}</lastmod>
    <priority>0.6</priority>
  </url>
@endforeach
</urlset>
