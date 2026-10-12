<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
use App\Models\Notice;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'articles' => Article::count(),
            'published' => Article::where('status', 'published')->count(),
            'drafts' => Article::where('status', 'draft')->count(),
            'review' => Article::where('status', 'review')->count(),
            'categories' => Category::count(),
            'notices' => Notice::count(),
            'recent' => Article::with('author')->latest('updated_at')->take(8)->get(),
        ]);
    }

    public function articles(Request $request)
    {
        $articles = Article::withTrashed()->with(['author', 'categories'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.addcslashes($request->string('q'), '\\%_').'%'))
            ->when(! $request->user()->isEditor(), fn ($query) => $query->where('author_id', $request->user()->id))
            ->latest('updated_at')->paginate(15)->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    public function createArticle()
    {
        return view('admin.articles.form', ['article' => new Article, 'categories' => Category::orderBy('name')->get(), 'tags' => '']);
    }

    public function storeArticle(Request $request)
    {
        $data = $this->validatedArticle($request);
        $article = DB::transaction(function () use ($request, $data) {
            $data['author_id'] = $request->user()->id;
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'], 'articles');
            $this->setArticlePublication($data);

            if ($request->hasFile('featured_image')) {
                $data['featured_image'] = $request->file('featured_image')->store('articles', 'public');
            }

            $article = Article::create($data);
            $article->categories()->sync($data['category_ids'] ?? []);
            $article->tags()->sync($this->syncTags($data['tags'] ?? ''));

            return $article;
        });

        return redirect()->route('admin.articles.edit', $article)->with('success', 'Article saved.');
    }

    public function editArticle(Request $request, Article $article)
    {
        $this->authorizeArticle($request, $article);

        return view('admin.articles.form', [
            'article' => $article->load(['categories', 'tags']),
            'categories' => Category::orderBy('name')->get(),
            'tags' => $article->tags->pluck('name')->implode(', '),
        ]);
    }

    public function updateArticle(Request $request, Article $article)
    {
        $this->authorizeArticle($request, $article);
        $data = $this->validatedArticle($request, $article);

        DB::transaction(function () use ($request, $article, $data) {
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'], 'articles', $article->id);
            $this->setArticlePublication($data, $article);

            if ($request->hasFile('featured_image')) {
                if ($article->featured_image) {
                    Storage::disk('public')->delete($article->featured_image);
                }
                $data['featured_image'] = $request->file('featured_image')->store('articles', 'public');
            }

            $article->update($data);
            $article->categories()->sync($data['category_ids'] ?? []);
            $article->tags()->sync($this->syncTags($data['tags'] ?? ''));
        });

        return back()->with('success', 'Article updated.');
    }

    public function destroyArticle(Request $request, Article $article)
    {
        $this->authorizeArticle($request, $article);
        $article->delete();

        return redirect()->route('admin.articles')->with('success', 'Article archived.');
    }

    public function restoreArticle(Request $request, int $articleId)
    {
        $article = Article::withTrashed()->findOrFail($articleId);
        $this->authorizeArticle($request, $article);

        if ($article->trashed()) {
            $article->restore();
        }
        $article->update(['status' => 'draft', 'published_at' => null, 'scheduled_at' => null]);

        return redirect()->route('admin.articles')->with('success', 'Article restored as a draft.');
    }

    public function categories()
    {
        return view('admin.categories', ['categories' => Category::withCount('articles')->orderBy('sort_order')->orderBy('name')->get()]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', 'unique:categories,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'], 'categories');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        Category::create($data);

        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn([$category->id])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'], 'categories', $category->id);
        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        abort_if($category->articles()->whereIn('status', ['published', 'scheduled'])->exists(), 422, 'Categories with published or scheduled articles cannot be deleted.');
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function tags()
    {
        return view('admin.tags', ['tags' => Tag::withCount('articles')->orderBy('name')->paginate(30)]);
    }

    public function storeTag(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', 'unique:tags,slug'],
        ]);
        Tag::create(['name' => $data['name'], 'slug' => $this->uniqueSlug($data['slug'] ?? $data['name'], 'tags')]);

        return back()->with('success', 'Tag created.');
    }

    public function updateTag(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('tags', 'slug')->ignore($tag->id)],
        ]);
        $tag->update([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['name'], 'tags', $tag->id),
        ]);

        return back()->with('success', 'Tag updated.');
    }

    public function destroyTag(Tag $tag)
    {
        $tag->delete();

        return back()->with('success', 'Tag deleted.');
    }

    public function notices()
    {
        $notices = Notice::with('author')->latest('updated_at')->paginate(15);

        return view('admin.notices.index', compact('notices'));
    }

    public function createNotice()
    {
        return view('admin.notices.form', ['notice' => new Notice]);
    }

    public function storeNotice(Request $request)
    {
        $data = $this->validatedNotice($request);
        $data['author_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'], 'notices');
        $data['is_important'] = $request->boolean('is_important');
        $this->setNoticePublication($data);
        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('notices', 'public');
        }
        Notice::create($data);

        return redirect()->route('admin.notices')->with('success', 'Notice saved.');
    }

    public function editNotice(Notice $notice)
    {
        return view('admin.notices.form', compact('notice'));
    }

    public function updateNotice(Request $request, Notice $notice)
    {
        $data = $this->validatedNotice($request, $notice);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'], 'notices', $notice->id);
        $data['is_important'] = $request->boolean('is_important');
        $this->setNoticePublication($data, $notice);
        if ($request->hasFile('attachment')) {
            if ($notice->attachment_path) {
                Storage::disk('public')->delete($notice->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('notices', 'public');
        }
        $notice->update($data);

        return back()->with('success', 'Notice updated.');
    }

    public function destroyNotice(Notice $notice)
    {
        if ($notice->attachment_path) {
            Storage::disk('public')->delete($notice->attachment_path);
        }
        $notice->delete();

        return back()->with('success', 'Notice deleted.');
    }

    public function media()
    {
        return view('admin.media', ['media' => Media::with('uploader')->latest()->paginate(24)]);
    }

    public function storeMedia(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'credit' => ['nullable', 'string', 'max:255'],
        ]);
        $file = $request->file('file');
        $path = $file->store('media', 'public');
        Media::create([
            'uploaded_by' => $request->user()->id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt_text' => $data['alt_text'] ?? null,
            'caption' => $data['caption'] ?? null,
            'credit' => $data['credit'] ?? null,
        ]);

        return back()->with('success', 'Image uploaded.');
    }

    public function destroyMedia(Media $media)
    {
        abort_if(
            Article::withTrashed()->where('featured_image', $media->path)->exists()
                || Category::where('image_path', $media->path)->exists(),
            422,
            'This image is still referenced by published content.'
        );

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return back()->with('success', 'Media deleted.');
    }

    public function settings(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate(['site_tagline' => ['required', 'string', 'max:180']]);
            DB::table('settings')->updateOrInsert(['key' => 'site_tagline'], ['value' => $data['site_tagline'], 'updated_at' => now(), 'created_at' => now()]);
            DB::table('settings')->updateOrInsert(['key' => 'breaking_ticker_enabled'], ['value' => $request->boolean('breaking_ticker_enabled') ? '1' : '0', 'updated_at' => now(), 'created_at' => now()]);

            return back()->with('success', 'Settings saved.');
        }

        return view('admin.settings', [
            'tagline' => DB::table('settings')->where('key', 'site_tagline')->value('value') ?: 'Your Trusted Source for Education News',
            'tickerEnabled' => DB::table('settings')->where('key', 'breaking_ticker_enabled')->value('value') !== '0',
        ]);
    }

    public function users()
    {
        return view('admin.users', ['users' => User::orderBy('name')->paginate(20)]);
    }

    public function storeUser(Request $request)
    {
        abort_unless($request->user()->role === 'super_admin', 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
            'role' => ['required', Rule::in(['administrator', 'editor', 'author', 'reporter'])],
        ]);
        User::create([...$data, 'password' => Hash::make($data['password'])]);

        return back()->with('success', 'User created.');
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless($request->user()->role === 'super_admin' && $request->user()->isNot($user), 403);
        $data = $request->validate(['role' => ['required', Rule::in(['administrator', 'editor', 'author', 'reporter'])]]);
        $user->update($data);

        return back()->with('success', 'User role updated.');
    }

    private function validatedArticle(Request $request, ?Article $article = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('articles', 'slug')->ignore($article?->id)],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['required', 'string', 'max:100000'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'image_caption' => ['nullable', 'string', 'max:255'],
            'image_credit' => ['nullable', 'string', 'max:255'],
            'source_name' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url:http,https', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'review', 'published', 'scheduled', 'archived'])],
            'scheduled_at' => ['nullable', 'date'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if (! $request->user()->isEditor() && in_array($data['status'], ['published', 'scheduled', 'archived'], true)) {
            $data['status'] = 'review';
        }

        $data['is_featured'] = $request->user()->isEditor() && $request->boolean('is_featured');
        $data['is_breaking'] = $request->user()->isEditor() && $request->boolean('is_breaking');

        return $data;
    }

    private function validatedNotice(Request $request, ?Notice $notice = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('notices', 'slug')->ignore($notice?->id)],
            'type' => ['required', Rule::in(['notice', 'scholarship', 'vacancy'])],
            'organization' => ['nullable', 'string', 'max:255'],
            'education_level' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'eligibility' => ['nullable', 'string', 'max:5000'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'source_url' => ['nullable', 'url:http,https', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'published_at' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);
    }

    private function setArticlePublication(array &$data, ?Article $existing = null): void
    {
        if ($data['status'] === 'published') {
            $data['published_at'] = $existing?->status === 'published' && $existing->published_at
                ? $existing->published_at : now();
            $data['scheduled_at'] = null;
        } elseif ($data['status'] === 'scheduled') {
            $data['scheduled_at'] = $data['scheduled_at'] ?? now()->addDay();
            $data['published_at'] = $data['scheduled_at'];
        } else {
            $data['published_at'] = null;
            $data['scheduled_at'] = null;
        }
    }

    private function setNoticePublication(array &$data, ?Notice $existing = null): void
    {
        if ($data['status'] === 'published') {
            $data['published_at'] = $data['published_at'] ?? ($existing?->published_at ?: now());
        } else {
            $data['published_at'] = null;
        }
    }

    private function authorizeArticle(Request $request, Article $article): void
    {
        abort_unless($request->user()->isEditor() || $article->author_id === $request->user()->id, 403);
    }

    private function syncTags(string $tags): array
    {
        return collect(explode(',', $tags))->map(fn ($tag) => trim($tag))->filter()
            ->unique(fn ($tag) => mb_strtolower($tag))->map(function ($name) {
                $slug = Str::slug($name) ?: 'tag-'.Str::lower(Str::random(10));
                $tag = Tag::firstOrCreate(['name' => $name], ['slug' => $this->uniqueSlug($slug, 'tags')]);

                return $tag->id;
            })->all();
    }

    private function uniqueSlug(string $value, string $table, ?int $ignoreId = null): string
    {
        $slug = Str::slug($value) ?: 'item-'.Str::lower(Str::random(10));
        $candidate = $slug;
        $suffix = 2;

        while (DB::table($table)->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $candidate = $slug.'-'.$suffix++;
        }

        return $candidate;
    }
}
