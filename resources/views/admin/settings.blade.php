@extends('layouts.admin')
@section('title', 'Site settings')
@section('heading', 'Site settings')
@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="article-paper form-grid" style="margin-top:22px">
        @csrf
        <div class="field"><label for="site_tagline">Site tagline</label><input id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $tagline) }}" required maxlength="180"></div>
        <label><input type="checkbox" name="breaking_ticker_enabled" value="1" @checked(old('breaking_ticker_enabled', $tickerEnabled))> Enable breaking-news ticker</label>
        <button class="button-primary">Save settings</button>
    </form>
@endsection
