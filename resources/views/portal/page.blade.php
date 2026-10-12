@extends('layouts.portal')

@section('title', $title.' | Shiksha Khabar')
@section('meta_description', $content)

@section('content')
    <section class="section article-paper">
        <h1 class="page-title">{{ $title }}</h1>
        <p class="article-body">{{ $content }}</p>
        @if($title === 'Contact us')
            <p>For corrections, please include the article link and reliable supporting evidence. Publish your organization's monitored editorial contact details here before launch.</p>
        @endif
    </section>
@endsection
