<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use App\Models\NewsPost;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;
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

        // Priority order + per-type limits
        $plan = [
            [
                'type' => 'Product',
                'model' => Product::class,
                'limit' => 8,
                'title_field' => 'name',
                'url' => fn ($m) => "/{$locale}/products/{$m->slug}",
                'image' => fn ($m) => null,
            ],
            [
                'type' => 'Industry',
                'model' => Industry::class,
                'limit' => 6,
                'title_field' => 'title',
                'url' => fn ($m) => "/{$locale}/industries/{$m->slug}",
                'image' => fn ($m) => $m->cover_image_path ? Storage::disk('public')->url($m->cover_image_path) : null,
            ],
            [
                'type' => 'News',
                'model' => NewsPost::class,
                'limit' => 6,
                'title_field' => 'title',
                'url' => fn ($m) => "/{$locale}/news/{$m->slug}",
                'image' => fn ($m) => null,
            ],
            [
                'type' => 'Page',
                'model' => Page::class,
                'limit' => 6,
                'title_field' => 'title',
                'url' => fn ($m) => "/{$locale}/pages/{$m->slug}",
                'image' => fn ($m) => null,
            ],
        ];

        $results = [];
        $seen = []; // de-dup by URL
        $maxTotal = 15;

        foreach ($plan as $cfg) {
            if (count($results) >= $maxTotal) break;

            $modelClass = $cfg['model'];
            $take = min($cfg['limit'], $maxTotal - count($results));

            try {
                $items = $modelClass::search($q)->take($take)->get();

                foreach ($items as $m) {
                    if (count($results) >= $maxTotal) break;

                    $field = $cfg['title_field'];
                    $raw = $m->{$field} ?? null;

                    $title = is_array($raw)
                        ? (data_get($raw, $locale) ?: data_get($raw, $fallback) ?: $m->slug)
                        : ((string) ($raw ?: $m->slug));

                    $url = ($cfg['url'])($m);

                    if (isset($seen[$url])) {
                        continue;
                    }
                    $seen[$url] = true;

                    $results[] = [
                        'type' => $cfg['type'],
                        'title' => $title,
                        'url' => $url,
                        'image' => ($cfg['image'])($m),
                    ];
                }
            } catch (\Throwable $e) {
                // Missing index / Meilisearch down / etc. => skip this type
                continue;
            }
        }

        return response()->json([
            'q' => $q,
            'results' => $results,
        ]);
    }
}