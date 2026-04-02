<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Industry extends Model
{
    use Searchable;

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'cover_image_path',
        'blocks',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'excerpt' => 'array',
        'blocks' => 'array',
        'is_published' => 'boolean',
    ];

    public function searchableAs(): string
    {
        return 'industries';
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_published;
    }

    public function toSearchableArray(): array
    {
        $title = $this->title ?? [];
        $excerpt = $this->excerpt ?? [];
        $blocks = $this->blocks ?? [];

        $text = trim(
            ($this->slug ?? '') . ' ' .
            implode(' ', array_filter(array_map('strval', (array) $title))) . ' ' .
            implode(' ', array_filter(array_map('strval', (array) $excerpt))) . ' ' .
            (is_array($blocks) ? json_encode($blocks, JSON_UNESCAPED_UNICODE) : (string) $blocks)
        );

        return [
            'type' => 'industries',
            'slug' => $this->slug,
            'title' => $title,
            'excerpt' => $excerpt,
            'sort_order' => (int) ($this->sort_order ?? 0),
            'text' => $text,
        ];
    }
}