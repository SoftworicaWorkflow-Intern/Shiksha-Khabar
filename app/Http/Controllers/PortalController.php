<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Notice;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortalController extends Controller
{
    public function home()
    {
        $featured = Article::publiclyVisible()->with(['author', 'categories'])
            ->where('is_featured', true)->latest('published_at')->take(3)->get();

        if ($featured->count() < 3) {
            $featured = $featured->concat(
                Article::publiclyVisible()->with(['author', 'categories'])
                    ->whereNotIn('id', $featured->pluck('id'))
                    ->latest('published_at')->take(3 - $featured->count())->get()
            );
        }

        return view('portal.home', [
            'featured' => $featured,
            'latest' => Article::publiclyVisible()->with(['author', 'categories'])->latest('published_at')->paginate(9),
            'breaking' => Article::publiclyVisible()->where('is_breaking', true)->latest('published_at')->take(5)->get(),
            'popular' => Article::publiclyVisible()->where('views', '>', 0)->orderByDesc('views')->take(5)->get(),
            'notices' => Notice::publiclyVisible()->latest('published_at')->take(5)->get(),
            'categories' => $this->navigationCategories(),
            'tickerEnabled' => DB::table('settings')->where('key', 'breaking_ticker_enabled')->value('value') !== '0',
        ]);
    }

    public function news()
    {
        return view('portal.listing', [
            'title' => 'Latest education news',
            'description' => 'The latest verified education news and updates from Nepal.',
            'articles' => Article::publiclyVisible()->with(['author', 'categories'])->latest('published_at')->paginate(12),
        ]);
    }

    public function article(string $slug)
    {
        $article = Article::publiclyVisible()->with(['author', 'categories', 'tags'])
            ->where('slug', $slug)->firstOrFail();
        $article->increment('views');

        $related = Article::publiclyVisible()->with(['categories'])
            ->where('id', '!=', $article->id)
            ->whereHas('categories', fn (Builder $query) => $query->whereIn('categories.id', $article->categories->modelKeys()))
            ->latest('published_at')->take(3)->get();

        return view('portal.article', [
            'article' => $article,
            'related' => $related,
            'previous' => Article::publiclyVisible()->where('published_at', '<', $article->published_at)->latest('published_at')->first(),
            'next' => Article::publiclyVisible()->where('published_at', '>', $article->published_at)->oldest('published_at')->first(),
        ]);
    }

    public function notice(string $slug)
    {
        $notice = Notice::publiclyVisible()->where('slug', $slug)->firstOrFail();

        return view('portal.notice', compact('notice'));
    }

    public function preview(Request $request, Article $article)
    {
        abort_unless($request->user()->isEditor() || $article->author_id === $request->user()->id, 403);

        return view('portal.article', [
            'article' => $article->load(['author', 'categories', 'tags']),
            'related' => collect(),
            'previous' => null,
            'next' => null,
        ]);
    }

    public function category(Category $category)
    {
        abort_unless($category->is_active, 404);

        return view('portal.listing', [
            'title' => $category->name,
            'description' => $category->description ?: "Education news in {$category->name}.",
            'articles' => $category->articles()->publiclyVisible()->with(['author', 'categories'])
                ->latest('published_at')->paginate(12),
        ]);
    }

    public function tag(Tag $tag)
    {
        return view('portal.listing', [
            'title' => '#'.$tag->name,
            'description' => "Articles tagged {$tag->name}.",
            'articles' => $tag->articles()->publiclyVisible()->with(['author', 'categories'])
                ->latest('published_at')->paginate(12),
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
        $term = trim($validated['q'] ?? '');
        $articles = Article::publiclyVisible()->with(['author', 'categories'])
            ->when($validated['category'] ?? null, fn (Builder $query, $category) => $query->whereHas('categories', fn (Builder $q) => $q->whereKey($category)))
            ->when($term !== '', function (Builder $query) use ($term) {
                $like = '%'.addcslashes($term, '\\%_').'%';
                $query->where(function (Builder $query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('body', 'like', $like)
                        ->orWhereHas('categories', fn (Builder $q) => $q->where('name', 'like', $like))
                        ->orWhereHas('tags', fn (Builder $q) => $q->where('name', 'like', $like));
                });
            })->latest('published_at')->paginate(12)->withQueryString();

        $notices = $term === '' ? collect() : Notice::publiclyVisible()
            ->where('title', 'like', '%'.addcslashes($term, '\\%_').'%')->latest('published_at')->take(5)->get();

        return view('portal.search', [
            'term' => $term,
            'articles' => $articles,
            'notices' => $notices,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function notices(string $type = 'notice')
    {
        abort_unless(in_array($type, ['notice', 'scholarship', 'vacancy'], true), 404);

        $title = match ($type) {
            'scholarship' => 'Scholarship opportunities',
            'vacancy' => 'Education vacancies',
            default => 'Notices & opportunities',
        };

        return view('portal.notices', [
            'title' => $title,
            'type' => $type,
            'notices' => Notice::publiclyVisible()->where('type', $type)->latest('published_at')->paginate(12),
        ]);
    }

    public function page(string $page)
    {
        $pages = [
            'about' => ['About Shiksha Khabar', 'An independent education news and information portal for Nepal.'],
            'contact' => ['Contact us', 'For editorial enquiries, corrections, and partnership requests, contact our team.'],
            'privacy-policy' => ['Privacy policy', 'We use information submitted through this website only to operate and improve the service.'],
            'terms' => ['Terms and conditions', 'Content is provided for information. Verify official announcements with the issuing organization.'],
            'editorial-policy' => ['Editorial policy', 'We aim to publish accurate, attributed education reporting and distinguish verified sources from opinion.'],
            'corrections-policy' => ['Corrections policy', 'Contact the editorial team with the article URL and supporting evidence to request a correction.'],
            'career-guidance' => ['Career guidance', 'Browse education news and verified career opportunities from our latest reporting.'],
            'admissions' => ['Admissions & entrance exams', 'Find published notices about admission dates, entrance examinations, and application deadlines.'],
            'exam-results' => ['Exam results', 'Education result reporting and official examination updates. Always confirm results with the issuing authority.'],
        ];
        abort_unless(isset($pages[$page]), 404);

        return view('portal.page', ['title' => $pages[$page][0], 'content' => $pages[$page][1]]);
    }

    public function sitemap()
    {
        return response()->view('portal.sitemap', [
            'articles' => Article::publiclyVisible()->latest('published_at')->get(['slug', 'updated_at']),
            'categories' => Category::where('is_active', true)->get(['slug', 'updated_at']),
        ])->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        return response(
            "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /login\n\nSitemap: ".route('sitemap')."\n",
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    private function navigationCategories()
    {
        return Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    }
}
