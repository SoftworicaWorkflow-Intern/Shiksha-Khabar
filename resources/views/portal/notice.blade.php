@extends('layouts.portal')

@section('title', $notice->title.' | Shiksha Khabar')
@section('meta_description', \Illuminate\Support\Str::limit($notice->instructions ?: $notice->title, 155))
@section('og_type', 'article')
@section('og_title', $notice->title)

@section('content')
    <p class="meta" style="margin-top:24px"><a href="{{ route('home') }}">Home</a> / <a href="{{ route($notice->type === 'scholarship' ? 'scholarships.index' : ($notice->type === 'vacancy' ? 'vacancies.index' : 'notices.index')) }}">{{ ucfirst($notice->type) }}</a></p>
    <article class="article-paper section">
        <span class="category-label">{{ ucfirst($notice->type) }} @if($notice->is_important) · Important notice @endif</span>
        <h1 class="article-title">{{ $notice->title }}</h1>
        <p class="meta">
            @if($notice->organization)<span>{{ $notice->organization }}</span>@endif
            @if($notice->published_at)<time datetime="{{ $notice->published_at->toIso8601String() }}">Published {{ $notice->published_at->format('F j, Y') }}</time>@endif
            @if($notice->deadline)<span class="deadline">{{ $notice->isExpired() ? 'Deadline passed' : 'Deadline' }} {{ $notice->deadline->format('F j, Y') }}</span>@endif
        </p>
        @if($notice->education_level || $notice->location)<p>{{ $notice->education_level }} @if($notice->location) · {{ $notice->location }}@endif</p>@endif
        @if($notice->eligibility)<h2>Eligibility</h2><p>{{ $notice->eligibility }}</p>@endif
        @if($notice->instructions)<h2>Details and instructions</h2><p style="white-space:pre-line">{{ $notice->instructions }}</p>@endif
        @if($notice->isExpired())<p class="deadline">This notice has expired. Check the official source for current information.</p>@endif
        <div class="admin-actions">
            @if($notice->source_url)<a class="button-primary" href="{{ $notice->source_url }}" target="_blank" rel="nofollow noopener">Verify with official source ↗</a>@endif
            @if($notice->attachment_path)<a class="button" href="{{ asset('storage/'.$notice->attachment_path) }}" target="_blank" rel="noopener">Open attached PDF</a>@endif
        </div>
    </article>
@endsection
