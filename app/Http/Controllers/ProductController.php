<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private function normList($v): array
    {
        if (is_array($v)) {
            return array_values(array_filter(array_map('trim', $v)));
        }

        if (is_string($v) && $v !== '') {
            return [trim($v)];
        }

        return [];
    }

    /**
     * Extract locale-specific list from either:
     * - legacy list: ["A","B"]
     * - locale map: {"en":["A"],"tr":["B"]}
     */
    private function extractFacetList($value, string $locale, string $fallback): array
    {
        if (is_array($value) && array_is_list($value)) {
            return $value;
        }

        if (is_array($value) && ! array_is_list($value)) {
            $list = data_get($value, $locale);
            if (! is_array($list)) {
                $list = data_get($value, $fallback);
            }

            return is_array($list) ? $list : [];
        }

        if (is_string($value) && trim($value) !== '') {
            return [trim($value)];
        }

        return [];
    }

    private function facetValues(string $locale): array
    {
        $fallback = config('locales.default', 'en');

        $products = Product::query()
            ->where('is_published', true)
            ->get([
                'industries',
                'applications',
                'product_groups',
                'processes',
                'sustainability_tags',
                'regulatory_tags',
            ]);

        $collect = function (string $key) use ($products, $locale, $fallback): array {
            return $products
                ->flatMap(fn ($p) => $this->extractFacetList($p->{$key} ?? null, $locale, $fallback))
                ->map(fn ($x) => trim((string) $x))
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();
        };

        return [
            'industries' => $collect('industries'),
            'applications' => $collect('applications'),
            'product_groups' => $collect('product_groups'),
            'processes' => $collect('processes'),
            'sustainability_tags' => $collect('sustainability_tags'),
            'regulatory_tags' => $collect('regulatory_tags'),
        ];
    }

    public function index(Request $request, string $locale)
    {
        $fallback = config('locales.default', 'en');

        $q = trim((string) $request->query('q', ''));
        $brandSlug = trim((string) $request->query('brand', ''));
        $sort = trim((string) $request->query('sort', 'relevance'));

        $filters = [
            'industries' => $this->normList($request->query('industries', [])),
            'applications' => $this->normList($request->query('applications', [])),
            'product_groups' => $this->normList($request->query('product_groups', [])),
            'processes' => $this->normList($request->query('processes', [])),
            'sustainability_tags' => $this->normList($request->query('sustainability_tags', [])),
            'regulatory_tags' => $this->normList($request->query('regulatory_tags', [])),
        ];

        $brand = null;

        $query = Product::query()
            ->with('brand')
            ->where('is_published', true)
            ->orderBy('sort_order');

        if ($brandSlug !== '') {
            $brand = Brand::query()
                ->where('slug', $brandSlug)
                ->where('is_published', true)
                ->first();

            if ($brand) {
                $query->where('brand_id', $brand->id);
            }
        }

        // Search (display_name may be JSON after multilingual)
        if ($q !== '') {
            $words = array_values(array_filter(array_map('trim', explode(' ', $q))));

            $query->where(function ($outer) use ($words, $locale, $fallback) {
                foreach ($words as $word) {
                    $lower = mb_strtolower($word);
                    $outer->where(function ($inner) use ($word, $lower, $locale, $fallback) {
                        $inner->whereRaw(
                            "LOWER(COALESCE(
                                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), ''),
                                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), '')
                            )) LIKE ?",
                            ["$.{$locale}", "$.{$fallback}", "%{$lower}%"]
                        )
                        ->orWhere('prd_number', 'like', "%{$word}%")
                        ->orWhere('slug',       'like', "%{$word}%");
                    });
                }
            });
        }

        // Facet filtering (support both legacy list and locale-map list)
        foreach ($filters as $col => $values) {
            if (! count($values)) {
                continue;
            }

            $query->where(function ($sub) use ($col, $values, $locale, $fallback) {
                foreach ($values as $v) {
                    $sub->orWhereJsonContains("{$col}->{$locale}", $v)
                        ->orWhereJsonContains("{$col}->{$fallback}", $v)
                        ->orWhereJsonContains($col, $v);
                }
            });
        }

        // Sorting
        if ($sort === 'newest') {
            $query->orderByDesc('id');
        } elseif ($sort === 'name_asc') {
            $query->orderByRaw(
                "COALESCE(
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), ''),
                    slug
                ) asc",
                ["$.{$locale}", "$.{$fallback}"]
            );
        } else {
            // "Most relevant"
            if ($q !== '') {
                $lowerQ = mb_strtolower($q);
                $query->orderByRaw(
                    "CASE
                        WHEN LOWER(COALESCE(
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), ''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), '')
                        )) LIKE ? THEN 0
                        WHEN LOWER(COALESCE(
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), ''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), '')
                        )) LIKE ? THEN 1
                        ELSE 2
                    END",
                    [
                        "$.{$locale}", "$.{$fallback}", "{$lowerQ}%",
                        "$.{$locale}", "$.{$fallback}", "%{$lowerQ}%",
                    ]
                );
            }

            $query->orderByDesc('id');
        }

        $products = $query->paginate(12)->withQueryString();

        $brands = Brand::query()
            ->where('is_published', true)
            ->orderBy('id')
            ->get();

        $facets = $this->facetValues($locale);

        return view('products.index', [
            'products' => $products,
            'brands' => $brands,
            'q' => $q,
            'brand' => $brand,
            'brandSlug' => $brandSlug,
            'sort' => $sort,
            'filters' => $filters,
            'facets' => $facets,
        ]);
    }

    public function show(Request $request, string $locale, string $slug)
    {
        $product = Product::query()
            ->with('brand')           // ← kept exactly as before
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $appUrl = rtrim((string) config('app.url'), '/');

        // Resolve display name for current locale
        $name = '';
        if (is_array($product->display_name)) {
            $name = $product->display_name[$locale]
                ?? $product->display_name['en']
                ?? $slug;
        } else {
            $name = (string) ($product->display_name ?? $slug);
        }

        // Resolve SEO fields from product's seo JSON column
        $seoCol   = is_array($product->seo) ? $product->seo : [];
        $seoTitle = $seoCol['title'][$locale]
            ?? $seoCol['title']['en']
            ?? $name . ' | Global Trading Ltd. Co.';
        $seoDesc  = $seoCol['description'][$locale]
            ?? $seoCol['description']['en']
            ?? '';

        // OG image
        $ogImage = !empty($product->cover_image_path)
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->cover_image_path)
            : $appUrl . '/images/og-default.png';

        $canonicalUrl = "{$appUrl}/{$locale}/products/{$slug}";

        // Schema
        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'        => 'Product',
                    '@id'          => $canonicalUrl . '#product',
                    'name'         => $name,
                    'description'  => $seoDesc ?: $name,
                    'url'          => $canonicalUrl,
                    'image'        => $ogImage,
                    'brand'        => [
                        '@type' => 'Brand',
                        'name'  => $product->brand?->name ?? 'Global Trading Ltd. Co.',
                    ],
                    'offers'       => [
                        '@type'         => 'Offer',
                        'priceCurrency' => 'USD',
                        'availability'  => 'https://schema.org/InStock',
                        'url'           => $canonicalUrl,
                        'seller'        => [
                            '@type' => 'Organization',
                            'name'  => 'Global Trading Ltd. Co.',
                        ],
                    ],
                ],
                [
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type'=>'ListItem','position'=>1,'name'=>'Home',    'item'=>"{$appUrl}/{$locale}"],
                        ['@type'=>'ListItem','position'=>2,'name'=>'Products','item'=>"{$appUrl}/{$locale}/products"],
                        ['@type'=>'ListItem','position'=>3,'name'=>$name,     'item'=>$canonicalUrl],
                    ],
                ],
            ],
        ];

        $seoMeta = [
            'title'       => mb_substr(html_entity_decode($seoTitle, ENT_QUOTES, 'UTF-8'), 0, 70),
            'description' => mb_substr(html_entity_decode($seoDesc,  ENT_QUOTES, 'UTF-8'), 0, 160),
            'canonical'   => $canonicalUrl,
            'ogImage'     => $ogImage,
            'ogType'      => 'product',
            'robots'      => 'index,follow',
            'schema'      => $schema,
        ];

        // $product is still passed — view continues working unchanged
        return view('products.show', compact('product', 'seoMeta'));
    }

    public function suggest(Request $request, string $locale): \Illuminate\Http\JsonResponse
    {
        $fallback = config('locales.default', 'en');
        $q        = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $words = array_values(array_filter(array_map('trim', explode(' ', $q))));

        $query = Product::query()
            ->where('is_published', true)
            ->select(['display_name', 'slug', 'prd_number', 'sort_order']);

        foreach ($words as $word) {
            $lower = mb_strtolower($word);
            $query->where(function ($inner) use ($lower, $word, $locale, $fallback) {
                $inner->whereRaw(
                    "LOWER(COALESCE(
                        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), ''),
                        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), '')
                    )) LIKE ?",
                    ["$.{$locale}", "$.{$fallback}", "%{$lower}%"]
                )
                ->orWhere('prd_number', 'like', "%{$word}%")
                ->orWhere('slug',       'like', "%{$word}%");
            });
        }

        $products = $query->orderBy('sort_order')->limit(12)->get();

        return response()->json(
            $products->map(fn ($p) => [
                'title' => data_get($p->display_name, $locale)
                        ?: data_get($p->display_name, $fallback)
                        ?: $p->slug,
                'prd'   => $p->prd_number,
                'url'   => "/{$locale}/products/{$p->slug}",
            ])
        );
    }
}