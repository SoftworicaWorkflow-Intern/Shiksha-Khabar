<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · Shiksha Khabar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="admin-shell">
    <aside class="admin-side">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-mark" aria-hidden="true"><strong>शि</strong></span>
            <span class="brand-copy"><strong lang="ne">शिक्षा खबर</strong><span>EDITORIAL DESK</span></span>
        </a>
        <nav class="admin-links" aria-label="Administration">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.articles') }}">Articles</a>
            @if(auth()->user()->isEditor())
                <a href="{{ route('admin.categories') }}">Categories</a>
                <a href="{{ route('admin.tags') }}">Tags</a>
                <a href="{{ route('admin.notices') }}">Notices & opportunities</a>
                <a href="{{ route('admin.media') }}">Media library</a>
            @endif
            @if(auth()->user()->canManageUsers())
                <a href="{{ route('admin.settings') }}">Site settings</a>
            @endif
            @if(auth()->user()->role === 'super_admin')
                <a href="{{ route('admin.users') }}">Users & roles</a>
            @endif
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-top">
            <div>
                <span class="meta">Signed in as {{ auth()->user()->name }} · {{ str_replace('_', ' ', ucfirst(auth()->user()->role)) }}</span>
                <h1 class="page-title" style="margin:4px 0 0">@yield('heading', 'Editorial dashboard')</h1>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="button" type="submit">Sign out</button></form>
        </div>
        @if(session('success'))<div class="flash" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="errors" role="alert"><strong>Please review the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
</body>
</html>
