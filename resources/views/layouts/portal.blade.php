<!doctype html>
<html lang="{{ app()->getLocale() === 'ne' ? 'ne' : 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Shiksha Khabar | Education News Nepal')</title>
    <meta name="description" content="@yield('meta_description', 'Education news, examination updates, scholarships and opportunities in Nepal.')">
    <link rel="canonical" href="@yield('canonical', request()->url())">
    <meta property="og:site_name" content="Shiksha Khabar">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title', 'Shiksha Khabar')))">
    <meta property="og:description" content="@yield('meta_description', 'Education news, examination updates, scholarships and opportunities in Nepal.')">
    <meta property="og:url" content="{{ request()->url() }}">
    @php($socialImage = trim($__env->yieldContent('og_image')))
    @if($socialImage)<meta property="og:image" content="{{ $socialImage }}">@endif
    <meta name="twitter:card" content="{{ $socialImage ? 'summary_large_image' : 'summary' }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<div class="utility">
    <div class="container utility-inner">
        <span>{{ now()->translatedFormat('l, F j, Y') }} <span lang="ne">| शिक्षा खबर</span></span>
        <div class="utility-links">
            <a href="{{ route('about') }}">About</a>
            <a href="{{ route('contact') }}">Contact</a>
            <a href="{{ route('login') }}">Staff sign in</a>
        </div>
    </div>
</div>
<header class="masthead">
    <div class="container masthead-inner">
        <a href="{{ route('home') }}" class="brand" aria-label="Shiksha Khabar home">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 10.5c7.1-2 13.2-.9 19 3.5v27c-5.8-4.4-11.9-5.5-19-3.5v-27Zm38 0c-7.1-2-13.2-.9-19 3.5v27c5.8-4.4 11.9-5.5 19-3.5v-27Z" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/>
                    <path d="M10 17c4-.7 7.5 0 10.5 2M10 23c4-.7 7.5 0 10.5 2M38 17c-4-.7-7.5 0-10.5 2M38 23c-4-.7-7.5 0-10.5 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="brand-copy">
                <strong lang="ne">शिक्षा खबर</strong>
                <span>SHIKSHA KHABAR</span>
                <small>{{ Illuminate\Support\Facades\DB::table('settings')->where('key', 'site_tagline')->value('value') ?: 'Your Trusted Source for Education News' }}</small>
            </span>
        </a>
        <form class="header-search" action="{{ route('search') }}" method="GET" role="search">
            <label class="skip-link" for="site-search">Search news</label>
            <input id="site-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search education news…" maxlength="120">
            <button type="submit">Search</button>
        </form>
        <button class="mobile-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle>Menu</button>
    </div>
</header>
<nav class="main-nav" aria-label="Main navigation">
    <div class="container">
        <ul class="nav-list" id="primary-navigation" data-menu>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('news.index') }}">Latest News</a></li>
            @foreach(($navCategories ?? \App\Models\Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->take(5)->get()) as $navCategory)
                <li><a href="{{ route('categories.show', $navCategory->slug) }}">{{ $navCategory->name }}</a></li>
            @endforeach
            <li><a href="{{ route('exam-results') }}">Exam Results</a></li>
            <li><a href="{{ route('scholarships.index') }}">Scholarships</a></li>
            <li><a href="{{ route('admissions') }}">Admissions</a></li>
            <li><a href="{{ route('notices.index') }}">Notices & Vacancies</a></li>
            <li><a href="{{ route('career-guidance') }}">Career Guidance</a></li>
        </ul>
    </div>
</nav>
<main id="main" class="container">
    @if(session('success'))<div class="flash" role="status">{{ session('success') }}</div>@endif
    @yield('content')
</main>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <section>
                <h2 lang="ne">शिक्षा खबर</h2>
                <p>Education reporting and useful opportunities for learners, families, and educators across Nepal.</p>
                <a href="{{ route('editorial-policy') }}">Our editorial policy</a>
            </section>
            <section>
                <h3>Explore</h3>
                <div class="footer-links">
                    <a href="{{ route('news.index') }}">Latest news</a>
                    <a href="{{ route('exam-results') }}">Exam results</a>
                    <a href="{{ route('scholarships.index') }}">Scholarships</a>
                    <a href="{{ route('vacancies.index') }}">Vacancies</a>
                </div>
            </section>
            <section>
                <h3>Information</h3>
                <div class="footer-links">
                    <a href="{{ route('about') }}">About us</a>
                    <a href="{{ route('contact') }}">Contact us</a>
                    <a href="{{ route('corrections-policy') }}">Corrections</a>
                    <a href="{{ route('privacy-policy') }}">Privacy policy</a>
                </div>
            </section>
            <section>
                <h3>Policies</h3>
                <div class="footer-links">
                    <a href="{{ route('terms') }}">Terms</a>
                    <a href="{{ route('editorial-policy') }}">Editorial policy</a>
                    <a href="{{ route('sitemap') }}">Sitemap</a>
                </div>
            </section>
        </div>
        <div class="footer-bottom">© {{ now()->year }} Shiksha Khabar. Verify official notices with the issuing organization.</div>
    </div>
</footer>
</body>
</html>
