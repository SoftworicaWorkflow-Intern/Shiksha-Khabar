@extends('layouts.portal')

@section('title', $title.' | Shiksha Khabar')
@section('meta_description', 'Published notices and verified education opportunities in Nepal.')

@section('content')
    <h1 class="page-title">{{ $title }}</h1>
    <p class="page-description">Check the issuing organization’s official source for the latest details before applying.</p>
    @if($notices->isEmpty())
        <div class="empty-state">No published {{ strtolower($title) }} are currently available.</div>
    @else
        <div class="notice-list">
            @foreach($notices as $notice)
                <article class="notice-item">
                    <div class="category-label">{{ ucfirst($notice->type) }} @if($notice->is_important) · Important notice @endif</div>
                    <h2><a href="{{ route('notices.show', $notice->slug) }}">{{ $notice->title }}</a></h2>
                    <div class="notice-meta">
                        @if($notice->organization){{ $notice->organization }} · @endif
                        @if($notice->published_at)Published {{ $notice->published_at->format('M j, Y') }}@endif
                        @if($notice->deadline) · <span class="deadline">{{ $notice->isExpired() ? 'Deadline passed' : 'Deadline' }} {{ $notice->deadline->format('M j, Y') }}</span>@endif
                    </div>
                    @if($notice->education_level || $notice->location)<p class="notice-meta">{{ $notice->education_level }} @if($notice->location) · {{ $notice->location }}@endif</p>@endif
                    @if($notice->eligibility)<p><strong>Eligibility:</strong> {{ $notice->eligibility }}</p>@endif
                    @if($notice->instructions)<p>{{ $notice->instructions }}</p>@endif
                    <div class="admin-actions">
                        @if($notice->source_url)<a class="button" href="{{ $notice->source_url }}" target="_blank" rel="nofollow noopener">Official source ↗</a>@endif
                        @if($notice->attachment_path)<a class="button" href="{{ asset('storage/'.$notice->attachment_path) }}" target="_blank" rel="noopener">View PDF</a>@endif
                    </div>
                </article>
            @endforeach
        </div>
        <div class="pagination">{{ $notices->links() }}</div>
    @endif
@endsection
