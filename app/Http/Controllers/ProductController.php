<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private function normList($v): array
    {
        if (is_array($v)) return array_values(array_filter(array_map('trim', $v)));
        if (is_string($v) && $v !== '') return [trim($v)];
        return [];
    }

    private function facetValues(): array
    {
        // Pull facets from all published products (fast enough for small/medium datasets).
        // If it grows big, we can cache or build a separate facets table.
        $products = Product::query()
            ->where('is_published', true)
            ->get([
                'industries','applications','product_groups','processes','sustainability_tags','regulatory_tags',
            ]);

        $collect = fn ($key) => $products
            ->flatMap(fn ($p) => (array)($p->{$key} ?? []))
            ->map(fn ($x) => trim((string)$x))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

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

            if ($brand) $query->where('brand_id', $brand->id);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('display_name', 'like', "%{$q}%")
                    ->orWhere('prd_number', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        // JSON facets filtering (OR across selected values per facet group, AND across facet groups)
        foreach ($filters as $col => $values) {
            if (!count($values)) continue;

            $query->where(function ($sub) use ($col, $values) {
                foreach ($values as $v) {
                    $sub->orWhereJsonContains($col, $v);
                }
            });
        }

        // Sorting
        if ($sort === 'newest') {
            $query->orderByDesc('id');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('display_name');
        } else {
            // "Most relevant" baseline: if q present, order by "starts with" then contains then newest.
            if ($q !== '') {
                $query->orderByRaw("CASE WHEN display_name LIKE ? THEN 0 WHEN display_name LIKE ? THEN 1 ELSE 2 END", ["{$q}%", "%{$q}%"]);
            }
            $query->orderByDesc('id');
        }

        $products = $query->paginate(12)->withQueryString();

        $brands = Brand::query()
            ->where('is_published', true)
            ->orderBy('id')
            ->get();

        $facets = $this->facetValues();

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