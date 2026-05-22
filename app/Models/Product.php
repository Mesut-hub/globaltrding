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
        // ── Core translatable strings ──────────────────────────────────────
        'name'         => 'array',
        'summary'      => 'array',
        'description'  => 'array',
        'display_name' => 'array',
        'industry_label' => 'array',

        // ── SEO (nested: seo.title.en, seo.description.en) ────────────────
        'seo' => 'array',

        // ── Translatable PDP section HTML (dot-notation in form:
        //    pdp_overview_html.en, pdp_properties_html.en, etc.) ───────────
        // BUG FIX: these were missing — without 'array' cast, Filament's
        // dot-notation Textarea::make("pdp_overview_html.$locale") cannot
        // read/write locale-specific values.
        'pdp_overview_html'      => 'array',
        'pdp_properties_html'    => 'array',
        'pdp_sustainability_html' => 'array',

        // ── Filter tag columns (locale → string[]) ─────────────────────────
        // Legacy flat list OR locale-map-of-lists; both are handled.
        'industries'        => 'array',
        'applications'      => 'array',
        'product_groups'    => 'array',
        'processes'         => 'array',
        'sustainability_tags' => 'array',
        'regulatory_tags'   => 'array',

        // ── PDP block builders ─────────────────────────────────────────────
        'pdp_documents'          => 'array',
        'pdp_overview_blocks'    => 'array',
        'pdp_properties_blocks'  => 'array',
        'pdp_documents_blocks'   => 'array',
        'pdp_sustainability_blocks' => 'array',

        // ── Access control toggles ─────────────────────────────────────────
        'pdp_public_overview'      => 'boolean',
        'pdp_public_properties'    => 'boolean',
        'pdp_public_documents'     => 'boolean',
        'pdp_public_sustainability' => 'boolean',

        'pdp_documents_logged_out_mode' => 'string',
        'is_published' => 'boolean',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────────

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Laravel Scout
    // ──────────────────────────────────────────────────────────────────────────

    public function searchableAs(): string
    {
        return 'products';
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_published;
    }

    public function toSearchableArray(): array
    {
        $fallback = config('locales.default', 'en');

        // Flatten a locale-keyed map {"en": "...", "tr": "..."} into a single search string
        $flattenLocaleMap = function (mixed $value) use ($fallback): string {
            if (! is_array($value)) {
                return trim((string) ($value ?? ''));
            }
            $out = [];
            if (isset($value[$fallback]) && is_scalar($value[$fallback])) {
                $out[] = (string) $value[$fallback];
            }
            foreach ($value as $k => $v) {
                if ($k === $fallback) continue;
                if (is_scalar($v)) $out[] = (string) $v;
            }
            return trim(implode(' ', array_filter(array_map('trim', $out))));
        };

        // Flatten a locale-map-of-lists {"en": [...], "tr": [...]} OR a plain list [...]
        $flattenAnyArray = function (mixed $value): string {
            if (! is_array($value)) {
                return trim((string) ($value ?? ''));
            }
            if (! array_is_list($value)) {
                $chunks = [];
                foreach ($value as $list) {
                    if (is_array($list)) $chunks[] = implode(' ', array_map('strval', $list));
                }
                return trim(implode(' ', $chunks));
            }
            return trim(implode(' ', array_map('strval', $value)));
        };

        $text = trim(implode(' ', array_filter([
            (string) ($this->slug ?? ''),
            $flattenLocaleMap($this->display_name),
            (string) ($this->prd_number ?? ''),
            $flattenLocaleMap($this->industry_label),
            $flattenAnyArray($this->industries),
            $flattenAnyArray($this->applications),
            $flattenAnyArray($this->product_groups),
            $flattenAnyArray($this->processes),
            $flattenAnyArray($this->sustainability_tags),
            $flattenAnyArray($this->regulatory_tags),
        ])));

        return [
            'type'           => 'product',
            'slug'           => $this->slug,
            'display_name'   => $this->display_name,
            'prd_number'     => $this->prd_number,
            'industry_label' => $this->industry_label,
            'text'           => $text,
        ];
    }
}