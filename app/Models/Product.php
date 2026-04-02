<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;

    protected $fillable = [
        'brand_id',
        'slug',
        'name',
        'summary',
        'description',
        'seo',
        'is_published',
    ];

    protected $casts = [
        'name' => 'array',
        'summary' => 'array',
        'description' => 'array',
        'seo' => 'array',
        'is_published' => 'boolean',
    ];

    public function searchableAs(): string
    {
        return 'products';
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_published;
    }

    public function toSearchableArray(): array
    {
        $name = $this->name ?? [];
        $summary = $this->summary ?? [];
        $description = $this->description ?? [];

        $text = trim(
            ($this->slug ?? '') . ' ' .
            implode(' ', array_filter(array_map('strval', (array) $name))) . ' ' .
            implode(' ', array_filter(array_map('strval', (array) $summary))) . ' ' .
            (is_array($description) ? json_encode($description, JSON_UNESCAPED_UNICODE) : (string) $description)
        );

        return [
            'type' => 'product',
            'slug' => $this->slug,
            'name' => $name,
            'summary' => $summary,
            'text' => $text,
        ];
    }
}