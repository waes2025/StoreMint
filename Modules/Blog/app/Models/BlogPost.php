<?php

namespace Modules\Blog\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'author_name',
        'image',
        'read_time_minutes',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    public function toBlogArray(): array
    {
        return [
            'id' => $this->getKey(),
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt ?: Str::limit(strip_tags($this->content), 160),
            'content' => $this->content,
            'category' => $this->category,
            'author' => $this->author_name ?: $this->author?->name ?: 'StoreMint Team',
            'date' => ($this->published_at ?: $this->created_at)->format('M d, Y'),
            'read_time' => $this->read_time_minutes.' min',
            'image' => $this->image ?: '/modules/blog/images/ecommerce.png',
            'is_published' => $this->isPublished(),
        ];
    }

    public function toFormArray(): array
    {
        return [
            'id' => $this->getKey(),
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'category' => $this->category,
            'author_name' => $this->author_name,
            'image' => $this->image,
            'is_published' => $this->published_at !== null,
        ];
    }
}
