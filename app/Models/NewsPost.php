<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class NewsPost extends Model
{
    use Searchable;

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'seo',
        'published_at',
        'is_published',
        'is_featured',
        'cover_image_path',
        'cover_video_path',
        'cover_poster_path',
    ];

    protected $casts = [
        'title' => 'array',
        'excerpt' => 'array',
        'content' => 'array',
        'seo' => 'array',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function searchableAs(): string
    {
        return 'news_posts';
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_published;
    }

    public function toSearchableArray(): array
    {
        $title = $this->title ?? [];
        $excerpt = $this->excerpt ?? [];
        $content = $this->content ?? [];

        $text = trim(
            ($this->slug ?? '') . ' ' .
            implode(' ', array_filter(array_map('strval', (array) $title))) . ' ' .
            implode(' ', array_filter(array_map('strval', (array) $excerpt))) . ' ' .
            (is_array($content) ? json_encode($content, JSON_UNESCAPED_UNICODE) : (string) $content)
        );

        return [
            'type' => 'news',
            'slug' => $this->slug,
            'title' => $title,
            'excerpt' => $excerpt,
            'published_at' => optional($this->published_at)->toDateTimeString(),
            'text' => $text,
        ];
    }
}