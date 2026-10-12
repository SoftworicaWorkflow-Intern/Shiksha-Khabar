@extends('layouts.admin')
@section('title', 'Tags')
@section('heading', 'Tag management')
@section('content')
    <section class="section article-paper">
        <h2>Create a tag</h2>
        <form method="POST" action="{{ route('admin.tags.store') }}" class="admin-actions">
            @csrf
            <input name="name" aria-label="Tag name" placeholder="Tag name" required maxlength="120">
            <input name="slug" aria-label="Tag URL slug (optional)" placeholder="Slug (optional)" maxlength="160">
            <button class="button-primary">Add tag</button>
        </form>
    </section>
    <div class="table-wrap"><table><thead><tr><th>Tag</th><th>Articles</th><th>Update</th><th>Delete</th></tr></thead><tbody>
        @forelse($tags as $tag)
            <tr><td colspan="4">
                <form method="POST" action="{{ route('admin.tags.update', $tag) }}" class="admin-actions">
                    @csrf @method('PUT')
                    <input name="name" aria-label="Name for {{ $tag->name }}" value="{{ $tag->name }}" required maxlength="120">
                    <input name="slug" aria-label="Slug for {{ $tag->name }}" value="{{ $tag->slug }}" required maxlength="160">
                    <span class="meta">{{ $tag->articles_count }} articles</span>
                    <button class="button">Save</button>
                </form>
                <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" style="margin-top:8px" onsubmit="return confirm('Remove this tag from articles?')">@csrf @method('DELETE')<button class="button button-danger">Delete</button></form>
            </td></tr>
        @empty<tr><td colspan="4">No tags yet. Tags are also created when attached to an article.</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination">{{ $tags->links() }}</div>
@endsection
