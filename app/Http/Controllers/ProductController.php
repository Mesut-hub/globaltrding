<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, string $locale)
    {
        $q = trim((string) $request->query('q', ''));
        $brandSlug = trim((string) $request->query('brand', ''));

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

        if ($q !== '') {
            // Simple JSON search (MVP): search only english name + current locale name
            $query->where(function ($sub) use ($q, $locale) {
                $sub->where("name->$locale", 'like', "%{$q}%")
                    ->orWhere("name->en", 'like', "%{$q}%");
            });
        }

        $products = $query
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $brands = Brand::query()
            ->where('is_published', true)
            ->orderBy('id')
            ->get();

        return view('products.index', compact('products', 'brands', 'q', 'brand', 'brandSlug'));
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