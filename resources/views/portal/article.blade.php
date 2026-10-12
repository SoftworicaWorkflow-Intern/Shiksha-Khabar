@extends('layouts.portal')

@section('title', ($article->meta_title ?: $article->title).' | Shiksha Khabar')
@section('meta_description', $article->meta_description ?: ($article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->body), 155)))
@section('og_type', 'article')
@section('og_title', $article->title)
@section('og_image', $article->featured_image ? asset('storage/'.$article->featured_image) : '')
@section('canonical', $article->canonical_url ?: route('news.show', $article->slug))

@push('head')
    <meta property="article:published_time" content="{{ $article->published_at?->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $article->updated_at->toIso8601String() }}">
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $article->title,
            'description' => $article->meta_description ?: $article->excerpt,
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $article->author->name],
            'publisher' => ['@type' => 'Organization', 'name' => 'Shiksha Khabar'],
            'mainEntityOfPage' => route('news.show', $article->slug),
        ];
        if ($article->featured_image) $schema['image'] = [asset('storage/'.$article->featured_image)];
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <div class="section">
        <p class="meta"><a href="{{ route('home') }}">Home</a> / <a href="{{ route('news.index') }}">News</a>
            @foreach($article->categories as $category)/ <a href="{{ route('categories.show', $category->slug) }}">{{ $category->name }}</a>@endforeach
        </p>
        <div class="two-col">
            <article class="article-paper">
                @if($article->categories->isNotEmpty())<a class="category-label" href="{{ route('categories.show', $article->categories->first()->slug) }}">{{ $article->categories->first()->name }}</a>@endif
                <h1 class="article-title">{{ $article->title }}</h1>
                @if($article->subtitle)<p class="article-subtitle">{{ $article->subtitle }}</p>@endif
                <div class="meta">
                    <span>By {{ $article->author->name }}</span>
                    <time datetime="{{ $article->published_at?->toIso8601String() }}">Published {{ $article->published_at?->format('F j, Y') }}</time>
                    @if($article->updated_at->gt($article->created_at->addMinutes(2)))<time datetime="{{ $article->updated_at->toIso8601String() }}">Updated {{ $article->updated_at->format('F j, Y') }}</time>@endif
                    <span>{{ max(1, (int) ceil(str_word_count(strip_tags($article->body)) / 220)) }} min read</span>
                </div>
                @if($article->featured_image)
                    <figure>
                        <img class="article-image" src="{{ asset('storage/'.$article->featured_image) }}" alt="{{ $article->image_alt ?: $article->title }}">
                        @if($article->image_caption || $article->image_credit)<figcaption class="caption">{{ $article->image_caption }} @if($article->image_credit) · Photo: {{ $article->image_credit }}@endif</figcaption>@endif
                    </figure>
                @endif
                @if($article->excerpt)<p class="article-subtitle">{{ $article->excerpt }}</p>@endif
                <div class="article-body">
                    @foreach(preg_split('/\R{2,}/u', $article->body) as $paragraph)
                        @if(trim($paragraph) !== '')<p>{{ trim($paragraph) }}</p>@endif
                    @endforeach
                </div>
                @if($article->source_url)
                    <p class="caption">Source: <a href="{{ $article->source_url }}" rel="nofollow noopener" target="_blank">{{ $article->source_name ?: 'Official source' }}</a></p>
                @endif
                @if($article->tags->isNotEmpty())
                    <p>Topics: @foreach($article->tags as $tag)<a class="button" href="{{ route('tags.show', $tag->slug) }}">{{ $tag->name }}</a> @endforeach</p>
                @endif
                <p class="meta"><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" rel="nofollow noopener" target="_blank">Share</a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}" rel="nofollow noopener" target="_blank">Post</a>
                    <button type="button" class="button" data-copy-link>Copy link</button>
                </p>
                <nav class="meta" aria-label="Article navigation">
                    @if($previous)<a href="{{ route('news.show', $previous->slug) }}">← Previous</a>@endif
                    @if($next)<a href="{{ route('news.show', $next->slug) }}">Next →</a>@endif
                </nav>
            </article>
            <aside>
                <div class="section-heading"><h2>Related stories</h2></div>
                @forelse($related as $item)
                    <article class="notice-item" style="margin-bottom:10px"><h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3><div class="notice-meta">{{ $item->published_at?->format('M j, Y') }}</div></article>
                @empty
                    <div class="empty-state">More stories will appear here as they are published.</div>
                @endforelse
            </aside>
        </div>
    </div>
@endsection
