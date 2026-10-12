@extends('layouts.portal')

@section('title', 'Search'.($term ? ': '.$term : '').' | Shiksha Khabar')
@section('meta_description', 'Search Shiksha Khabar education news, articles and notices.')

@section('content')
    <h1 class="page-title">Search</h1>
    <form class="header-search" action="{{ route('search') }}" method="GET" role="search">
        <input type="search" name="q" value="{{ $term }}" aria-label="Search articles and notices" placeholder="Search news, categories, tags…" maxlength="120">
        <select name="category" aria-label="Filter results by category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
        <button type="submit">Search</button>
    </form>
    @if($term !== '')
        <p class="page-description">Results for “{{ $term }}”</p>
        @if($articles->count())
            <div class="news-grid">@foreach($articles as $article) @include('partials.article-card', ['article' => $article]) @endforeach</div>
            <div class="pagination">{{ $articles->links() }}</div>
        @else
            <div class="empty-state">No published articles match that search.</div>
        @endif
        @if($notices->isNotEmpty())
            <section class="section"><div class="section-heading"><h2>Matching notices</h2></div><div class="notice-list">
                @foreach($notices as $notice)<article class="notice-item"><h3><a href="{{ route('notices.show', $notice->slug) }}">{{ $notice->title }}</a></h3><div class="notice-meta">{{ $notice->organization }}</div></article>@endforeach
            </div></section>
        @endif
    @else
        <p class="page-description">Enter a title, topic, category, or tag to search.</p>
    @endif
@endsection
