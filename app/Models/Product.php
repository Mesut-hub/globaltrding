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
        'display_name',
        'display_url',
        'prd_number',
        'industry_label',
        'industries',
        'applications',
        'product_groups',
        'processes',
        'sustainability_tags',
        'regulatory_tags',
        'pdp_overview_html',
        'pdp_properties_html',
        'pdp_documents',
        'pdp_sustainability_html',
        'pdp_overview_blocks',
        'pdp_properties_blocks',
        'pdp_documents_blocks',
        'pdp_sustainability_blocks',
        'pdp_public_overview',
        'pdp_public_properties',
        'pdp_public_documents',
        'pdp_public_sustainability',
        'pdp_documents_logged_out_mode',
    ];

    protected $casts = [
        'name' => 'array',
        'summary' => 'array',
        'description' => 'array',
        'seo' => 'array',
        'is_published' => 'boolean',
        'display_name' => 'array',
        'industry_label' => 'array',
        'industries' => 'array',
        'applications' => 'array',
        'product_groups' => 'array',
        'processes' => 'array',
        'sustainability_tags' => 'array',
        'regulatory_tags' => 'array',
        'pdp_documents' => 'array',
        'pdp_overview_blocks' => 'array',
        'pdp_properties_blocks' => 'array',
        'pdp_documents_blocks' => 'array',
        'pdp_sustainability_blocks' => 'array',
        'pdp_public_overview' => 'boolean',
        'pdp_public_properties' => 'boolean',
        'pdp_public_documents' => 'boolean',
        'pdp_public_sustainability' => 'boolean',
        'pdp_documents_logged_out_mode' => 'string',
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
        $text = trim(
            ($this->slug ?? '') . ' ' .
            ($this->display_name ?? '') . ' ' .
            ($this->prd_number ?? '') . ' ' .
            ($this->industry_label ?? '') . ' ' .
            implode(' ', (array)($this->industries ?? [])) . ' ' .
            implode(' ', (array)($this->applications ?? [])) . ' ' .
            implode(' ', (array)($this->product_groups ?? [])) . ' ' .
            implode(' ', (array)($this->processes ?? [])) . ' ' .
            implode(' ', (array)($this->sustainability_tags ?? [])) . ' ' .
            implode(' ', (array)($this->regulatory_tags ?? []))
        );

        return [
            'type' => 'product',
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'prd_number' => $this->prd_number,
            'industry_label' => $this->industry_label,
            'text' => $text,
        ];
    }
}