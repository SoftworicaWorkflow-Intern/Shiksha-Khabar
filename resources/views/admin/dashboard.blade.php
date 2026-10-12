@extends('layouts.admin')
@section('title', 'Dashboard')
@section('heading', 'Dashboard overview')
@section('content')
    <div class="stats-grid">
        <div class="stat-card"><span>Total articles</span><strong>{{ $articles }}</strong></div>
        <div class="stat-card"><span>Published</span><strong>{{ $published }}</strong></div>
        <div class="stat-card"><span>Drafts</span><strong>{{ $drafts }}</strong></div>
        <div class="stat-card"><span>Awaiting review</span><strong>{{ $review }}</strong></div>
        <div class="stat-card"><span>Categories</span><strong>{{ $categories }}</strong></div>
        <div class="stat-card"><span>Notices</span><strong>{{ $notices }}</strong></div>
    </div>
    <section class="section">
        <div class="section-heading"><h2>Recently updated stories</h2><a href="{{ route('admin.articles') }}">Manage articles →</a></div>
        <div class="table-wrap"><table><thead><tr><th>Headline</th><th>Author</th><th>Status</th><th>Updated</th><th></th></tr></thead>
            <tbody>@forelse($recent as $article)<tr><td>{{ $article->title }}</td><td>{{ $article->author->name }}</td><td>{{ ucfirst($article->status) }}</td><td>{{ $article->updated_at->format('M j, Y H:i') }}</td><td><a href="{{ route('admin.articles.edit', $article) }}">Edit</a></td></tr>
                @empty<tr><td colspan="5">No articles yet. Create an article to begin publishing.</td></tr>@endforelse</tbody></table></div>
    </section>
@endsection
