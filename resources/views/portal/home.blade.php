@extends('layouts.portal')

@section('title', 'Shiksha Khabar | Education News and Opportunities in Nepal')
@section('meta_description', 'Verified education news, exam updates, scholarships, admissions and opportunities from Nepal.')

@section('content')
    @if($tickerEnabled && $breaking->isNotEmpty())
        <section class="ticker" aria-label="Breaking news">
            <strong class="ticker-label">BREAKING NEWS</strong>
            <div class="ticker-items">
                @foreach($breaking as $item)
                    <a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="section" aria-labelledby="top-stories">
        <div class="section-heading"><h2 id="top-stories">Top stories</h2><a href="{{ route('news.index') }}">All news →</a></div>
        @if($featured->isEmpty())
            <div class="empty-state">
                <h1>Education news, thoughtfully reported.</h1>
                <p>Shiksha Khabar brings together education updates, examination information and opportunities across Nepal.</p>
                <a class="button-primary" href="{{ route('notices.index') }}">Browse notices</a>
            </div>
        @else
            <div class="hero-grid">
                @php($lead = $featured->first())
                <article class="feature-main">
                    <a href="{{ route('news.show', $lead->slug) }}">
                        @if($lead->featured_image)
                            <img class="article-image" style="margin:0;max-height:none" src="{{ asset('storage/'.$lead->featured_image) }}" alt="{{ $lead->image_alt ?: $lead->title }}" fetchpriority="high">
                        @else
                            <div class="media-placeholder"><span>शिक्षा खबर</span></div>
                        @endif
                    </a>
                    <div class="feature-main-copy">
                        @if($lead->categories->isNotEmpty())<a class="category-label" href="{{ route('categories.show', $lead->categories->first()->slug) }}">{{ $lead->categories->first()->name }}</a>@endif
                        <h1><a href="{{ route('news.show', $lead->slug) }}">{{ $lead->title }}</a></h1>
                        @if($lead->excerpt)<p>{{ $lead->excerpt }}</p>@endif
                        <div class="meta"><time datetime="{{ $lead->published_at?->toIso8601String() }}">{{ $lead->published_at?->format('M j, Y') }}</time>@if($lead->author)<span>{{ $lead->author->name }}</span>@endif</div>
                    </div>
                </article>
                <div class="feature-stack">
                    @foreach($featured->skip(1) as $item)
                        <article class="feature-small">
                            <a href="{{ route('news.show', $item->slug) }}">
                                @if($item->featured_image)
                                    <img class="article-image" style="margin:0;max-height:none;height:100%;min-height:125px" src="{{ asset('storage/'.$item->featured_image) }}" alt="{{ $item->image_alt ?: $item->title }}" loading="lazy">
                                @else
                                    <div class="media-placeholder"><span>SK</span></div>
                                @endif
                            </a>
                            <div class="feature-small-copy">
                                @if($item->categories->isNotEmpty())<span class="category-label">{{ $item->categories->first()->name }}</span>@endif
                                <h2><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h2>
                                <div class="meta"><time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->format('M j') }}</time></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <section class="section" aria-labelledby="latest-news">
        <div class="section-heading"><h2 id="latest-news">Latest news</h2><a href="{{ route('news.index') }}">View all →</a></div>
        @if($latest->count())
            <div class="news-grid">@foreach($latest as $article) @include('partials.article-card', ['article' => $article]) @endforeach</div>
            <div class="pagination">{{ $latest->links() }}</div>
        @else
            <div class="empty-state">No stories have been published yet. Please check back soon.</div>
        @endif
    </section>

    <section class="section two-col">
        <div>
            <div class="section-heading"><h2>Latest notices & opportunities</h2><a href="{{ route('notices.index') }}">All notices →</a></div>
            @if($notices->isEmpty())
                <div class="empty-state">No notices are currently available.</div>
            @else
                <ul class="side-list">@foreach($notices as $notice)
                    <li><a href="{{ route('notices.show', $notice->slug) }}"><strong>{{ $notice->title }}</strong></a>
                        <div class="notice-meta">{{ $notice->organization }} @if($notice->deadline) · Deadline {{ $notice->deadline->format('M j, Y') }} @endif</div>
                    </li>
                @endforeach</ul>
            @endif
        </div>
        <aside>
            <div class="section-heading"><h2>Explore topics</h2></div>
            <div class="footer-links">@foreach($categories as $category)<a class="button" href="{{ route('categories.show', $category->slug) }}">{{ $category->name }}</a>@endforeach</div>
        </aside>
    </section>

    <section class="section">
        <div class="section-heading"><h2>Most read</h2></div>
        @if($popular->isEmpty())
            <div class="empty-state">Reader statistics will appear after published stories receive visits.</div>
        @else
            <ol class="side-list">@foreach($popular as $article)
                <li><a href="{{ route('news.show', $article->slug) }}"><strong>{{ $article->title }}</strong></a> <span class="meta">{{ number_format($article->views) }} reads</span></li>
            @endforeach</ol>
        @endif
    </section>
@endsection
