<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Page extends Model
{
    use Searchable;

    protected $fillable = [
        'slug',
        'title',
        'content',
        'blocks',
        'seo',
        'is_published',
        'show_in_company',
        'show_in_products',
        'show_in_information',
        'show_in_service',
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'blocks' => 'array',
        'seo' => 'array',
        'is_published' => 'boolean',
        'show_in_company' => 'boolean',
        'show_in_products' => 'boolean',
        'show_in_information' => 'boolean',
        'show_in_service' => 'boolean',
    ];

    public function searchableAs(): string
    {
        return 'pages';
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_published;
    }

    public function toSearchableArray(): array
    {
        $title = $this->title ?? [];
        $content = $this->content ?? [];

        $text = trim(
            ($this->slug ?? '') . ' ' .
            implode(' ', array_filter(array_map('strval', (array) $title))) . ' ' .
            (is_array($content) ? json_encode($content, JSON_UNESCAPED_UNICODE) : (string) $content)
        );

        return [
            'type' => 'page',
            'slug' => $this->slug,
            'title' => $title,
            'text' => $text,
        ];
    }
}