<article class="news-card">
    <a href="{{ route('news.show', $article->slug) }}" aria-label="{{ $article->title }}">
        @if($article->featured_image)
            <img src="{{ asset('storage/'.$article->featured_image) }}" alt="{{ $article->image_alt ?: $article->title }}" loading="lazy">
        @else
            <div class="media-placeholder" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none"><path d="M8 10h32v28H8zM8 31l10-10 8 8 5-5 9 9" stroke="currentColor" stroke-width="2"/></svg>
            </div>
        @endif
    </a>
    <div class="news-card-copy">
        @if($article->categories->isNotEmpty())
            <a class="category-label" href="{{ route('categories.show', $article->categories->first()->slug) }}">{{ $article->categories->first()->name }}</a>
        @endif
        <h3><a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a></h3>
        @if($article->excerpt)<p>{{ $article->excerpt }}</p>@endif
        <div class="meta"><time datetime="{{ $article->published_at?->toIso8601String() }}">{{ $article->published_at?->format('M j, Y') }}</time>@if($article->author) <span>By {{ $article->author->name }}</span>@endif</div>
    </div>
</article>
