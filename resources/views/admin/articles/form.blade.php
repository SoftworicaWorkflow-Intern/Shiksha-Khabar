@extends('layouts.admin')
@section('title', $article->exists ? 'Edit article' : 'Create article')
@section('heading', $article->exists ? 'Edit article' : 'Create article')
@section('content')
    <form method="POST" enctype="multipart/form-data" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}" class="article-paper form-grid">
        @csrf
        @if($article->exists) @method('PUT') @endif
        <div class="field"><label for="title">Headline</label><input id="title" name="title" value="{{ old('title', $article->title) }}" required maxlength="255"></div>
        <div class="field"><label for="subtitle">Subtitle (optional)</label><input id="subtitle" name="subtitle" value="{{ old('subtitle', $article->subtitle) }}" maxlength="255"></div>
        <div class="field"><label for="slug">URL slug (optional)</label><input id="slug" name="slug" value="{{ old('slug', $article->slug) }}" maxlength="180"><span class="helper-text">Nepali-only headlines receive a safe generated URL slug.</span></div>
        <div class="field"><label for="excerpt">Summary</label><textarea id="excerpt" name="excerpt" maxlength="1000">{{ old('excerpt', $article->excerpt) }}</textarea></div>
        <div class="field"><label for="body">Article text</label><textarea id="body" name="body" required maxlength="100000">{{ old('body', $article->body) }}</textarea><span class="helper-text">Plain text is safely escaped on publication. Separate paragraphs with a blank line.</span></div>
        <div class="field"><label for="category_ids">Categories</label><select id="category_ids" name="category_ids[]" multiple size="6">@foreach($categories as $category)<option value="{{ $category->id }}" @selected(collect(old('category_ids', $article->categories->modelKeys()))->contains($category->id))>{{ $category->name }}</option>@endforeach</select></div>
        <div class="field"><label for="tags">Tags</label><input id="tags" name="tags" value="{{ old('tags', $tags) }}" placeholder="scholarship, examination, policy"></div>
        <div class="field"><label for="featured_image">Featured image</label><input id="featured_image" type="file" name="featured_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">@if($article->featured_image)<img src="{{ asset('storage/'.$article->featured_image) }}" alt="{{ $article->image_alt ?: 'Current article image' }}" style="width:180px">@endif</div>
        <div class="field"><label for="image_alt">Image alt text</label><input id="image_alt" name="image_alt" value="{{ old('image_alt', $article->image_alt) }}" maxlength="255"></div>
        <div class="field"><label for="image_caption">Image caption</label><input id="image_caption" name="image_caption" value="{{ old('image_caption', $article->image_caption) }}" maxlength="255"></div>
        <div class="field"><label for="image_credit">Image credit</label><input id="image_credit" name="image_credit" value="{{ old('image_credit', $article->image_credit) }}" maxlength="255"></div>
        <div class="field"><label for="source_name">Source name</label><input id="source_name" name="source_name" value="{{ old('source_name', $article->source_name) }}" maxlength="255"></div>
        <div class="field"><label for="source_url">Source URL</label><input id="source_url" type="url" name="source_url" value="{{ old('source_url', $article->source_url) }}" maxlength="2048"></div>
        <div class="field"><label for="meta_title">SEO title</label><input id="meta_title" name="meta_title" value="{{ old('meta_title', $article->meta_title) }}" maxlength="255"></div>
        <div class="field"><label for="meta_description">SEO description</label><textarea id="meta_description" name="meta_description" maxlength="320">{{ old('meta_description', $article->meta_description) }}</textarea></div>
        <div class="field"><label for="canonical_url">Canonical URL</label><input id="canonical_url" type="url" name="canonical_url" value="{{ old('canonical_url', $article->canonical_url) }}" maxlength="2048"></div>
        <div class="field"><label for="status">Editorial status</label><select id="status" name="status" required>@foreach(['draft','review','published','scheduled','archived'] as $status)<option value="{{ $status }}" @selected(old('status', $article->status ?: 'draft') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="field"><label for="scheduled_at">Schedule publication</label><input id="scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $article->scheduled_at?->format('Y-m-d\TH:i')) }}"></div>
        @if(auth()->user()->isEditor())
            <label><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $article->is_featured))> Feature on homepage</label>
            <label><input type="checkbox" name="is_breaking" value="1" @checked(old('is_breaking', $article->is_breaking))> Mark as breaking news</label>
        @endif
        <div class="admin-actions"><button class="button-primary" type="submit">Save article</button>@if($article->exists)<a class="button" href="{{ route('admin.articles.preview', $article) }}" target="_blank">Preview</a>@endif<a class="button" href="{{ route('admin.articles') }}">Back to articles</a></div>
    </form>
@endsection
