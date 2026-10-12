<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortalController::class, 'home'])->name('home');
Route::get('/news', [PortalController::class, 'news'])->name('news.index');
Route::get('/news/{slug}', [PortalController::class, 'article'])->name('news.show');
Route::get('/category/{category:slug}', [PortalController::class, 'category'])->name('categories.show');
Route::get('/tag/{tag:slug}', [PortalController::class, 'tag'])->name('tags.show');
Route::get('/search', [PortalController::class, 'search'])->name('search');
Route::get('/notices', [PortalController::class, 'notices'])->name('notices.index');
Route::get('/notices/{slug}', [PortalController::class, 'notice'])->name('notices.show');
Route::get('/scholarships', fn () => app(PortalController::class)->notices('scholarship'))->name('scholarships.index');
Route::get('/vacancies', fn () => app(PortalController::class)->notices('vacancy'))->name('vacancies.index');
Route::get('/exam-results', fn () => app(PortalController::class)->page('exam-results'))->name('exam-results');
Route::get('/admissions', fn () => app(PortalController::class)->page('admissions'))->name('admissions');
Route::get('/career-guidance', fn () => app(PortalController::class)->page('career-guidance'))->name('career-guidance');
Route::get('/about', fn () => app(PortalController::class)->page('about'))->name('about');
Route::get('/contact', fn () => app(PortalController::class)->page('contact'))->name('contact');
Route::get('/privacy-policy', fn () => app(PortalController::class)->page('privacy-policy'))->name('privacy-policy');
Route::get('/terms', fn () => app(PortalController::class)->page('terms'))->name('terms');
Route::get('/editorial-policy', fn () => app(PortalController::class)->page('editorial-policy'))->name('editorial-policy');
Route::get('/corrections-policy', fn () => app(PortalController::class)->page('corrections-policy'))->name('corrections-policy');
Route::get('/sitemap.xml', [PortalController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PortalController::class, 'robots'])->name('robots');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'portal-role:super_admin,administrator,editor,author,reporter'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/articles', [AdminController::class, 'articles'])->name('articles');
    Route::get('/articles/create', [AdminController::class, 'createArticle'])->name('articles.create');
    Route::post('/articles', [AdminController::class, 'storeArticle'])->name('articles.store');
    Route::get('/articles/{article}/edit', [AdminController::class, 'editArticle'])->name('articles.edit');
    Route::get('/articles/{article}/preview', [PortalController::class, 'preview'])->name('articles.preview');
    Route::put('/articles/{article}', [AdminController::class, 'updateArticle'])->name('articles.update');
    Route::post('/articles/{article}/restore', [AdminController::class, 'restoreArticle'])->name('articles.restore');
    Route::delete('/articles/{article}', [AdminController::class, 'destroyArticle'])->name('articles.destroy');

    Route::middleware('portal-role:super_admin,administrator,editor')->group(function () {
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('/tags', [AdminController::class, 'tags'])->name('tags');
        Route::post('/tags', [AdminController::class, 'storeTag'])->name('tags.store');
        Route::put('/tags/{tag}', [AdminController::class, 'updateTag'])->name('tags.update');
        Route::delete('/tags/{tag}', [AdminController::class, 'destroyTag'])->name('tags.destroy');
        Route::get('/notices', [AdminController::class, 'notices'])->name('notices');
        Route::get('/notices/create', [AdminController::class, 'createNotice'])->name('notices.create');
        Route::post('/notices', [AdminController::class, 'storeNotice'])->name('notices.store');
        Route::get('/notices/{notice}/edit', [AdminController::class, 'editNotice'])->name('notices.edit');
        Route::put('/notices/{notice}', [AdminController::class, 'updateNotice'])->name('notices.update');
        Route::delete('/notices/{notice}', [AdminController::class, 'destroyNotice'])->name('notices.destroy');
        Route::get('/media', [AdminController::class, 'media'])->name('media');
        Route::post('/media', [AdminController::class, 'storeMedia'])->name('media.store');
        Route::delete('/media/{media}', [AdminController::class, 'destroyMedia'])->name('media.destroy');
    });

    Route::middleware('portal-role:super_admin,administrator')->group(function () {
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'settings'])->name('settings.update');
    });
    Route::middleware('portal-role:super_admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    });
});
