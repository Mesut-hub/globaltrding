<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Industry;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    public function index(Request $request, string $locale)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json(['q' => $q, 'results' => []]);
        }

        $fallback = config('locales.default', 'en');
        $needle = mb_strtolower($q);

        $results = [];

        // Pages (title JSON)
        $pages = Page::query()
            ->where('is_published', true)
            ->where(function ($sub) use ($needle, $locale, $fallback) {
                $sub->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, ?))) LIKE ?', ['$.'.$locale, "%{$needle}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, ?))) LIKE ?', ['$.'.$fallback, "%{$needle}%"]);
            })
            ->limit(6)
            ->get();

        foreach ($pages as $p) {
            $title = data_get($p->title, $locale) ?: data_get($p->title, $fallback) ?: $p->slug;

            $results[] = [
                'type' => 'Page',
                'title' => $title,
                'url' => "/{$locale}/pages/{$p->slug}",
                'image' => null,
            ];
        }

        // News (title JSON)
        $news = NewsPost::query()
            ->where('is_published', true)
            ->where(function ($sub) use ($needle, $locale, $fallback) {
                $sub->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, ?))) LIKE ?', ['$.'.$locale, "%{$needle}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, ?))) LIKE ?', ['$.'.$fallback, "%{$needle}%"]);
            })
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        foreach ($news as $n) {
            $title = data_get($n->title, $locale) ?: data_get($n->title, $fallback) ?: $n->slug;

            $results[] = [
                'type' => 'News',
                'title' => $title,
                'url' => "/{$locale}/news/{$n->slug}",
                'image' => null,
            ];
        }

        // Products (name JSON)
        $products = Product::query()
            ->where('is_published', true)
            ->where(function ($sub) use ($needle, $locale, $fallback) {
                $sub->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, ?))) LIKE ?', ['$.'.$locale, "%{$needle}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, ?))) LIKE ?', ['$.'.$fallback, "%{$needle}%"]);
            })
            ->limit(8)
            ->get();

        foreach ($products as $p) {
            $title = data_get($p->name, $locale) ?: data_get($p->name, $fallback) ?: $p->slug;

            $results[] = [
                'type' => 'Product',
                'title' => $title,
                'url' => "/{$locale}/products/{$p->slug}",
                'image' => null,
            ];
        }

        return response()->json([
            'q' => $q,
            'results' => $results,
        ]);

        $industries = Industry::query()
            ->where('is_published', true)
            ->where(function ($sub) use ($needle, $locale, $fallback) {
                $sub->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, ?))) LIKE ?', ['$.'.$locale, "%{$needle}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, ?))) LIKE ?', ['$.'.$fallback, "%{$needle}%"]);
            })
            ->limit(6)
            ->get();

        foreach ($industries as $i) {
            $title = data_get($i->title, $locale) ?: data_get($i->title, $fallback) ?: $i->slug;

            $results[] = [
                'type' => 'Industry',
                'title' => $title,
                'url' => "/{$locale}/industries/{$i->slug}",
                'image' => $i->cover_image_path ? Storage::disk('public')->url($i->cover_image_path) : null,
            ];
        }

        // Industries
        $industries = Industry::query()
            ->where('is_published', true)
            ->where(function ($sub) use ($needle, $locale, $fallback) {
                $sub->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, ?))) LIKE ?', ['$.'.$locale, "%{$needle}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, ?))) LIKE ?', ['$.'.$fallback, "%{$needle}%"]);
            })
            ->limit(6)
            ->get();

        foreach ($industries as $i) {
            $title = data_get($i->title, $locale) ?: data_get($i->title, $fallback) ?: $i->slug;

            $results[] = [
                'type' => 'Industry',
                'title' => $title,
                'url' => "/{$locale}/industries", // later: /industries/{slug}
                'image' => $i->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($i->image_path) : null,
            ];
        }
    }
}