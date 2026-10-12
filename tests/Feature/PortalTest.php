<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_only_displays_published_articles(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        Article::create([
            'author_id' => $editor->id,
            'title' => 'Verified published story',
            'slug' => 'verified-published-story',
            'body' => 'A published education update.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        Article::create([
            'author_id' => $editor->id,
            'title' => 'Unpublished draft story',
            'slug' => 'unpublished-draft-story',
            'body' => 'A draft.',
            'status' => 'draft',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Verified published story')
            ->assertDontSee('Unpublished draft story');
    }

    public function test_article_route_renders_published_content_safely_and_adds_seo_metadata(): void
    {
        $author = User::factory()->create(['name' => 'News Desk']);
        $article = Article::create([
            'author_id' => $author->id,
            'title' => 'Education update',
            'slug' => 'education-update',
            'body' => '<script>alert("xss")</script>'."\n\n".'Second paragraph.',
            'excerpt' => 'A verified report.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $this->get(route('news.show', $article->slug))
            ->assertOk()
            ->assertSee('Education update')
            ->assertSee('News Desk')
            ->assertSee('A verified report.')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertSee('application/ld+json', false);
    }

    public function test_unpublished_articles_are_not_accessible_by_slug(): void
    {
        $author = User::factory()->create();
        $article = Article::create([
            'author_id' => $author->id,
            'title' => 'Editorial draft',
            'slug' => 'editorial-draft',
            'body' => 'Private draft text.',
            'status' => 'review',
        ]);

        $this->get(route('news.show', $article->slug))->assertNotFound();
    }

    public function test_search_finds_published_article_and_filters_by_category(): void
    {
        $author = User::factory()->create();
        $category = Category::create(['name' => 'Scholarships', 'slug' => 'scholarships']);
        $article = Article::create([
            'author_id' => $author->id,
            'title' => 'नेपाल छात्रवृत्ति अवसर',
            'slug' => 'nepal-scholarship-opportunity',
            'body' => 'Scholarship application information.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        $article->categories()->attach($category);

        $this->get(route('search', ['q' => 'छात्रवृत्ति', 'category' => $category->id]))
            ->assertOk()
            ->assertSee('नेपाल छात्रवृत्ति अवसर');

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('नेपाल छात्रवृत्ति अवसर');
    }

    public function test_reporter_can_submit_for_review_but_cannot_publish(): void
    {
        $reporter = User::factory()->create(['role' => 'reporter']);

        $this->actingAs($reporter)->post(route('admin.articles.store'), [
            'title' => 'Reporter submission',
            'body' => 'Awaiting editorial review.',
            'status' => 'published',
        ])->assertRedirect();

        $this->assertDatabaseHas('articles', [
            'title' => 'Reporter submission',
            'author_id' => $reporter->id,
            'status' => 'review',
            'published_at' => null,
        ]);
    }

    public function test_reporter_cannot_manage_categories_or_view_another_authors_article(): void
    {
        $reporter = User::factory()->create(['role' => 'reporter']);
        $other = User::factory()->create(['role' => 'author']);
        $article = Article::create([
            'author_id' => $other->id,
            'title' => 'Another authors draft',
            'slug' => 'another-authors-draft',
            'body' => 'Private content.',
        ]);

        $this->actingAs($reporter)->get(route('admin.categories'))->assertForbidden();
        $this->actingAs($reporter)->get(route('admin.articles.edit', $article))->assertForbidden();
    }

    public function test_notice_publication_expiry_and_official_source_are_rendered(): void
    {
        $author = User::factory()->create();
        $notice = Notice::create([
            'author_id' => $author->id,
            'title' => 'Official examination schedule',
            'slug' => 'official-examination-schedule',
            'type' => 'notice',
            'organization' => 'Education Board',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'source_url' => 'https://example.org/notice',
        ]);

        $this->get(route('notices.show', $notice->slug))
            ->assertOk()
            ->assertSee('Official examination schedule')
            ->assertSee('Education Board')
            ->assertSee('https://example.org/notice');
    }

    public function test_media_upload_rejects_non_images_and_saves_valid_image(): void
    {
        Storage::fake('public');
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->create('notes.pdf', 25, 'application/pdf'),
        ])->assertSessionHasErrors('file');

        $this->actingAs($editor)->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->create('school.png', 10, 'image/png'),
            'alt_text' => 'Students in a classroom',
        ])->assertRedirect();

        $this->assertDatabaseHas('media', [
            'uploaded_by' => $editor->id,
            'original_name' => 'school.png',
            'alt_text' => 'Students in a classroom',
        ]);
        $this->assertCount(1, Storage::disk('public')->allFiles('media'));
    }

    public function test_logged_out_users_are_redirected_from_admin(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_editorial_admin_screens_render_for_an_editor(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $this->actingAs($editor);

        foreach ([
            'admin.dashboard',
            'admin.articles',
            'admin.articles.create',
            'admin.categories',
            'admin.tags',
            'admin.notices',
            'admin.notices.create',
            'admin.media',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_deleted_article_can_only_be_restored_as_a_draft(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $article = Article::create([
            'author_id' => $editor->id,
            'title' => 'Recoverable article',
            'slug' => 'recoverable-article',
            'body' => 'Article content.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        $article->delete();

        $this->actingAs($editor)->post(route('admin.articles.restore', $article->id))->assertRedirect();

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'draft',
            'published_at' => null,
            'deleted_at' => null,
        ]);
        $this->get(route('news.show', $article->slug))->assertNotFound();
    }

    public function test_robots_and_sitemap_routes_use_the_configured_origin(): void
    {
        $sitemapUrl = route('sitemap');

        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Sitemap: '.$sitemapUrl);
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8');
    }
}
