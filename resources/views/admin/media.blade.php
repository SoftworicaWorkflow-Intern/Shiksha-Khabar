@extends('layouts.admin')
@section('title', 'Media library')
@section('heading', 'Media library')
@section('content')
    <section class="section article-paper">
        <h2>Upload an image</h2>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.media.store') }}" class="form-grid">
            @csrf
            <div class="field"><label for="file">Image (JPEG, PNG or WebP; max 5 MB)</label><input id="file" type="file" name="file" accept="image/jpeg,image/png,image/webp" required></div>
            <div class="field"><label for="alt_text">Alt text</label><input id="alt_text" name="alt_text" maxlength="255"></div>
            <div class="field"><label for="caption">Caption</label><input id="caption" name="caption" maxlength="255"></div>
            <div class="field"><label for="credit">Credit</label><input id="credit" name="credit" maxlength="255"></div>
            <button class="button-primary">Upload</button>
        </form>
    </section>
    <div class="media-grid">@forelse($media as $item)<article class="media-tile"><img src="{{ asset('storage/'.$item->path) }}" alt="{{ $item->alt_text ?: '' }}" loading="lazy"><p><strong>{{ $item->original_name }}</strong></p><p class="meta">{{ $item->caption }} {{ $item->credit ? '· '.$item->credit : '' }}</p><label class="helper-text">Public URL</label><input readonly value="{{ asset('storage/'.$item->path) }}" onclick="this.select()"><form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Delete this image?')">@csrf @method('DELETE')<button class="button button-danger" style="margin-top:8px" type="submit">Delete</button></form></article>@empty<div class="empty-state">The media library is empty.</div>@endforelse</div>
    <div class="pagination">{{ $media->links() }}</div>
@endsection
