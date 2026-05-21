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
            ->where('is_published', true);

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
            $query->where(function ($sub) use ($q, $locale, $fallback) {
                $sub->whereRaw(
                    "COALESCE(
                        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), ''),
                        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), '')
                    ) LIKE ?",
                    ["$.{$locale}", "$.{$fallback}", "%{$q}%"]
                )
                ->orWhere('prd_number', 'like', "%{$q}%")
                ->orWhere('slug', 'like', "%{$q}%");
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
                $query->orderByRaw(
                    "CASE
                        WHEN COALESCE(
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), ''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), '')
                        ) LIKE ? THEN 0
                        WHEN COALESCE(
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), ''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(display_name, ?)), '')
                        ) LIKE ? THEN 1
                        ELSE 2
                     END",
                    [
                        "$.{$locale}", "$.{$fallback}", "{$q}%",
                        "$.{$locale}", "$.{$fallback}", "%{$q}%",
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
            ->with('brand')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('products.show', compact('product'));
    }
}