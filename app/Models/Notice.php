<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    protected $fillable = [
        'author_id', 'title', 'slug', 'type', 'organization', 'education_level',
        'location', 'eligibility', 'instructions', 'source_url', 'attachment_path',
        'status', 'is_important', 'published_at', 'deadline', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_important' => 'boolean',
            'published_at' => 'datetime',
            'deadline' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $query) {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', today());
            });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function isExpired(): bool
    {
        return ($this->expires_at?->lt(today()) ?? false)
            || ($this->deadline?->lt(today()) ?? false);
    }
}
