@extends('layouts.admin')
@section('title', $notice->exists ? 'Edit notice' : 'Create notice')
@section('heading', $notice->exists ? 'Edit notice' : 'Create notice')
@section('content')
    <form method="POST" enctype="multipart/form-data" action="{{ $notice->exists ? route('admin.notices.update', $notice) : route('admin.notices.store') }}" class="article-paper form-grid">
        @csrf @if($notice->exists) @method('PUT') @endif
        <div class="field"><label for="title">Title</label><input id="title" name="title" value="{{ old('title', $notice->title) }}" required maxlength="255"></div>
        <div class="field"><label for="slug">URL slug (optional)</label><input id="slug" name="slug" value="{{ old('slug', $notice->slug) }}" maxlength="180"></div>
        <div class="field"><label for="type">Type</label><select id="type" name="type">@foreach(['notice'=>'Notice','scholarship'=>'Scholarship','vacancy'=>'Vacancy'] as $key=>$label)<option value="{{ $key }}" @selected(old('type', $notice->type ?: 'notice') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="organization">Issuing organization</label><input id="organization" name="organization" value="{{ old('organization', $notice->organization) }}" maxlength="255"></div>
        <div class="field"><label for="education_level">Education level</label><input id="education_level" name="education_level" value="{{ old('education_level', $notice->education_level) }}" maxlength="120"></div>
        <div class="field"><label for="location">Location</label><input id="location" name="location" value="{{ old('location', $notice->location) }}" maxlength="120"></div>
        <div class="field"><label for="eligibility">Eligibility</label><textarea id="eligibility" name="eligibility" maxlength="5000">{{ old('eligibility', $notice->eligibility) }}</textarea></div>
        <div class="field"><label for="instructions">Application instructions or details</label><textarea id="instructions" name="instructions" maxlength="5000">{{ old('instructions', $notice->instructions) }}</textarea></div>
        <div class="field"><label for="source_url">Official source URL</label><input id="source_url" type="url" name="source_url" value="{{ old('source_url', $notice->source_url) }}" maxlength="2048"></div>
        <div class="field"><label for="attachment">Official notice PDF (optional)</label><input id="attachment" type="file" name="attachment" accept="application/pdf,.pdf"></div>
        <div class="field"><label for="published_at">Publication date</label><input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', $notice->published_at?->format('Y-m-d\TH:i')) }}"></div>
        <div class="field"><label for="deadline">Application deadline</label><input id="deadline" type="date" name="deadline" value="{{ old('deadline', $notice->deadline?->format('Y-m-d')) }}"></div>
        <div class="field"><label for="expires_at">Notice expiry</label><input id="expires_at" type="date" name="expires_at" value="{{ old('expires_at', $notice->expires_at?->format('Y-m-d')) }}"></div>
        <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="draft" @selected(old('status', $notice->status ?: 'draft') === 'draft')>Draft</option><option value="published" @selected(old('status', $notice->status) === 'published')>Published</option><option value="archived" @selected(old('status', $notice->status) === 'archived')>Archived</option></select></div>
        <label><input type="checkbox" name="is_important" value="1" @checked(old('is_important', $notice->is_important))> Mark as important</label>
        <div class="admin-actions"><button class="button-primary">Save notice</button><a class="button" href="{{ route('admin.notices') }}">Cancel</a></div>
    </form>
@endsection
