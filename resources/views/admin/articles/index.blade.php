@extends('layouts.admin')
@section('title', 'Articles')
@section('heading', 'Article management')
@section('content')
    <div class="admin-actions" style="margin:20px 0">
        <form action="{{ route('admin.articles') }}" method="GET" class="header-search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search headlines" aria-label="Search headlines">
            <select name="status" aria-label="Filter by status"><option value="">All statuses</option>@foreach(['draft','review','published','scheduled','archived'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
            <button type="submit">Filter</button>
        </form>
        <a class="button-primary" href="{{ route('admin.articles.create') }}">Create article</a>
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>Headline</th><th>Categories</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
        <tbody>@forelse($articles as $article)
            <tr>
                <td><strong>{{ $article->title }}</strong><br><span class="meta">By {{ $article->author->name }}</span></td>
                <td>{{ $article->categories->pluck('name')->join(', ') ?: '—' }}</td>
                <td>{{ $article->trashed() ? 'Deleted' : ucfirst($article->status) }} @if($article->scheduled_at)<br><span class="meta">{{ $article->scheduled_at->format('M j, Y H:i') }}</span>@endif</td>
                <td>{{ $article->updated_at->format('M j, Y') }}</td>
                <td><div class="admin-actions">
                    @if($article->trashed() || $article->status === 'archived')
                        <form method="POST" action="{{ route('admin.articles.restore', $article->id) }}">@csrf<button class="button" type="submit">Restore as draft</button></form>
                    @else
                        <a class="button" href="{{ route('admin.articles.edit', $article) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Move this article to the recoverable archive?')">@csrf @method('DELETE')<button class="button button-danger" type="submit">Archive</button></form>
                    @endif
                    </div></td>
            </tr>
        @empty<tr><td colspan="5">No articles match this filter.</td></tr>@endforelse</tbody>
    </table></div>
    <div class="pagination">{{ $articles->links() }}</div>
@endsection
