<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'author_id', 'title', 'slug', 'subtitle', 'excerpt', 'body', 'featured_image',
        'image_alt', 'image_caption', 'image_credit', 'status', 'is_featured',
        'is_breaking', 'published_at', 'scheduled_at', 'source_name', 'source_url',
        'meta_title', 'meta_description', 'canonical_url',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_breaking' => 'boolean',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            })->orWhere(function (Builder $query) {
                $query->where('status', 'scheduled')
                    ->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '<=', now());
            });
        });
    }

    public function isPubliclyVisible(): bool
    {
        return ($this->status === 'published' && $this->published_at?->isPast())
            || ($this->status === 'scheduled' && $this->scheduled_at?->isPast());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
