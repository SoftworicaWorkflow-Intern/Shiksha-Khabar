{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc></url>
    <url><loc>{{ route('news.index') }}</loc></url>
    @foreach($categories as $category)
        <url><loc>{{ route('categories.show', $category->slug) }}</loc><lastmod>{{ $category->updated_at->toAtomString() }}</lastmod></url>
    @endforeach
    @foreach($articles as $article)
        <url><loc>{{ route('news.show', $article->slug) }}</loc><lastmod>{{ $article->updated_at->toAtomString() }}</lastmod></url>
    @endforeach
    @foreach(\App\Models\Notice::publiclyVisible()->get(['slug', 'updated_at']) as $notice)
        <url><loc>{{ route('notices.show', $notice->slug) }}</loc><lastmod>{{ $notice->updated_at->toAtomString() }}</lastmod></url>
    @endforeach
</urlset>
