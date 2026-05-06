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

    public function setTitleAttribute($value): void
    {
        // Accept array (Filament), JSON string, or plain string
        if (is_string($value)) {
            $trim = trim($value);
            if ($trim !== '' && ($trim[0] === '{' || $trim[0] === '[')) {
                $decoded = json_decode($trim, true);
                $value = is_array($decoded) ? $decoded : ['en' => $value];
            } else {
                $value = ['en' => $value];
            }
        }

        $this->attributes['title'] = json_encode($value ?: [], JSON_UNESCAPED_UNICODE);
    }

    public function setExcerptAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['excerpt'] = null;
            return;
        }

        if (is_string($value)) {
            $trim = trim($value);
            if ($trim !== '' && ($trim[0] === '{' || $trim[0] === '[')) {
                $decoded = json_decode($trim, true);
                $value = is_array($decoded) ? $decoded : ['en' => $value];
            } else {
                $value = ['en' => $value];
            }
        }

        $this->attributes['excerpt'] = json_encode($value ?: [], JSON_UNESCAPED_UNICODE);
    }

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