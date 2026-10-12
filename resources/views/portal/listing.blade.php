@extends('layouts.portal')

@section('title', $title.' | Shiksha Khabar')
@section('meta_description', $description)

@section('content')
    <h1 class="page-title">{{ $title }}</h1>
    <p class="page-description">{{ $description }}</p>
    @if($articles->count())
        <div class="news-grid">@foreach($articles as $article) @include('partials.article-card', ['article' => $article]) @endforeach</div>
        <div class="pagination">{{ $articles->links() }}</div>
    @else
        <div class="empty-state">No published stories in this section yet.</div>
    @endif
@endsection
