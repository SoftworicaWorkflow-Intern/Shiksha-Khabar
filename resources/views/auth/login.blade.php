@extends('layouts.portal')

@section('title', 'Staff sign in | Shiksha Khabar')
@section('content')
    <div class="login-wrap article-paper">
        <h1 class="page-title" style="margin-top:0">Staff sign in</h1>
        <p class="page-description">Sign in to manage editorial content.</p>
        @if($errors->any())<div class="errors" role="alert">The email or password was not accepted.</div>@endif
        <form method="POST" action="{{ route('login.store') }}" class="form-grid">
            @csrf
            <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"></div>
            <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password"></div>
            <label><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button class="button-primary" type="submit">Sign in</button>
        </form>
    </div>
@endsection
