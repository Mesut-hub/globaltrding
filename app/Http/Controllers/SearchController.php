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
        try {
            $q = trim((string) $request->query('q', ''));

            if (mb_strlen($q) < 3) {
                return response()->json(['q' => $q, 'results' => []]);
            }

            $fallback = config('locales.default', 'en');
            $qNorm = $this->norm($q);
            $qLen = mb_strlen($qNorm);
            $containsOnly = ($qLen === 3);
            $qTokens = $this->tokens($qNorm);
            $allowFuzzyFallback = $qLen >= 5;
            $minScore = $qLen <= 4 ? 420 : 260;

            $plan = [
                [
                    'type' => 'Product',
                    'model' => Product::class,
                    'limit' => 8,
                    'title_field' => 'display_name',
                    'extra_fields' => ['industry_label'],
                    'url' => fn ($m) => "/{$locale}/products/{$m->slug}",
                    'image' => fn ($m) => null,
                ],
                [
                    'type' => 'Industry',
                    'model' => Industry::class,
                    'limit' => 6,
                    'title_field' => 'title',
                    'extra_fields' => ['excerpt', 'blocks'],
                    'url' => fn ($m) => "/{$locale}/industries/{$m->slug}",
                    'image' => fn ($m) => $m->cover_image_path ? Storage::disk('public')->url($m->cover_image_path) : null,
                ],
                [
                    'type' => 'News',
                    'model' => NewsPost::class,
                    'limit' => 6,
                    'title_field' => 'title',
                    'extra_fields' => ['excerpt', 'content'],
                    'url' => fn ($m) => "/{$locale}/news/{$m->slug}",
                    'image' => fn ($m) => null,
                ],
                [
                    'type' => 'Page',
                    'model' => Page::class,
                    'limit' => 6,
                    'title_field' => 'title',
                    'extra_fields' => ['content'],
                    'url' => fn ($m) => "/{$locale}/pages/{$m->slug}",
                    'image' => fn ($m) => null,
                ],
            ];

            $results = [];
            $seen = [];
            $maxTotal = 15;
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';

            foreach ($plan as $cfg) {
                if (count($results) >= $maxTotal) break;

                $modelClass = $cfg['model'];
                $take = min($cfg['limit'], $maxTotal - count($results));
                $fields = array_merge([$cfg['title_field']], $cfg['extra_fields'] ?? []);

                $items = $modelClass::query()
                    ->where('is_published', true)
                    ->where(function ($qq) use ($like, $fields, $locale, $fallback) {
                        $qq->where('slug', 'like', $like);
                        foreach ($fields as $field) {
                            $qq->orWhereRaw(
                                "JSON_UNQUOTE(JSON_EXTRACT(`$field`, ?)) LIKE ?",
                                ['$."' . $locale . '"', $like]
                            );
                            if ($fallback !== $locale) {
                                $qq->orWhereRaw(
                                    "JSON_UNQUOTE(JSON_EXTRACT(`$field`, ?)) LIKE ?",
                                    ['$."' . $fallback . '"', $like]
                                );
                            }
                        }
                    })
                    ->limit(80)
                    ->get();

                if ($items->isEmpty() && $allowFuzzyFallback) {
                    $items = $modelClass::query()
                        ->where('is_published', true)
                        ->orderByDesc('updated_at')
                        ->limit(200)
                        ->get();
                }

                $scored = [];
                foreach ($items as $m) {
                    try {
                        $title = $this->locText($m->{$cfg['title_field']} ?? null, $locale, $fallback);
                        $slug = (string) ($m->slug ?? '');
                        $titleText = $this->norm(trim($slug . ' ' . $title));

                        $extra = [];
                        foreach (($cfg['extra_fields'] ?? []) as $f) {
                            $extra[] = $this->locText($m->{$f} ?? null, $locale, $fallback);
                        }
                        $extraText = $this->norm(implode(' ', array_filter($extra)));

                        $scoreTitle = $this->strongScore($qNorm, $qTokens, $titleText);
                        $scoreExtra = $this->strongScore($qNorm, $qTokens, $extraText);
                        $score = (int) round($scoreTitle * 1.0 + $scoreExtra * 0.45);

                        if ($score < $minScore || $score <= 0) {
                            continue;
                        }

                        $url = ($cfg['url'])($m);
                        if (isset($seen[$url])) continue;

                        $scored[] = [
                            'score' => $score,
                            'title' => $title !== '' ? $title : ($slug !== '' ? $slug : 'Untitled'),
                            'url' => $url,
                            'image' => ($cfg['image'])($m),
                            'type' => $cfg['type'],
                        ];
                    } catch (\Exception $e) {
                        // Log error but continue processing other items
                        $itemId = $m->id ?? 'unknown';
                        \Log::warning("Search: Error processing {$cfg['type']} item {$itemId}: " . $e->getMessage());
                        continue;
                    }
                }

                usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

                foreach (array_slice($scored, 0, $take) as $row) {
                    if (count($results) >= $maxTotal) break;
                    $seen[$row['url']] = true;
                    $results[] = [
                        'type' => $row['type'],
                        'title' => $row['title'],
                        'url' => $row['url'],
                        'image' => $row['image'],
                    ];
                }
            }

            return response()->json([
                'q' => $q,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            \Log::error("Search controller error: " . $e->getMessage(), [
                'query' => $request->query('q'),
                'locale' => $locale,
                'exception' => $e,
            ]);

            return response()->json([
                'q' => $request->query('q', ''),
                'results' => [],
                'error' => 'Search error occurred. Please try again.',
            ], 500);
        }
    }

    private function norm(string $s): string
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return trim($s);
    }

    private function tokens(string $s): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $s) ?: [];
        return array_values(array_filter($parts, fn ($x) => $x !== '' && mb_strlen($x) >= 2));
    }

    private function locText($raw, string $locale, string $fallback): string
    {
        if (is_array($raw)) {
            return (string) (data_get($raw, $locale) ?: data_get($raw, $fallback) ?: '');
        }
        return (string) ($raw ?: '');
    }

    private function strongScore(string $qNorm, array $qTokens, string $text): int
    {
        $t = $text;
        if ($t === '') return 0;

        if ($t === $qNorm) return 1000;
        if (str_starts_with($t, $qNorm)) return 930;
        if (str_contains($t, $qNorm)) return 860;

        $prefixBoost = 0;
        foreach ($qTokens as $tok) {
            if ($tok === '') continue;
            if (preg_match('/\b' . preg_quote($tok, '/') . '/u', $t)) {
                $prefixBoost += 40;
            }
        }

        $words = array_slice($this->tokens($t), 0, 80);

        $tokenScore = 0;
        foreach ($qTokens as $tok) {
            $best = 0;
            $tokLen = mb_strlen($tok);

            $maxDist = 0;
            if ($tokLen <= 3) $maxDist = 0;
            elseif ($tokLen <= 6) $maxDist = 1;
            elseif ($tokLen <= 10) $maxDist = 2;
            else $maxDist = 3;

            foreach ($words as $w) {
                if ($w === $tok) { $best = 140; break; }

                if (str_starts_with($w, $tok) || str_starts_with($tok, $w)) {
                    $best = max($best, 120);
                    continue;
                }

                $dist = levenshtein(mb_substr($tok, 0, 40), mb_substr($w, 0, 40));
                if ($dist <= $maxDist) {
                    $best = max($best, 110 - ($dist * 25));
                }
            }

            $tokenScore += $best;
        }

        $tri = $this->trigramSimilarity($qNorm, mb_substr($t, 0, 260));
        $triScore = (int) round($tri * 220);

        $final = $prefixBoost + $tokenScore + $triScore;
        return max(0, min(999, $final));
    }

    private function trigramSimilarity(string $a, string $b): float
    {
        $a = $this->padForTrigram($a);
        $b = $this->padForTrigram($b);

        $A = $this->trigrams($a);
        $B = $this->trigrams($b);

        if (!$A || !$B) return 0.0;

        $setA = array_count_values($A);
        $setB = array_count_values($B);

        $inter = 0;
        $union = 0;

        $keys = array_unique(array_merge(array_keys($setA), array_keys($setB)));
        foreach ($keys as $k) {
            $ca = $setA[$k] ?? 0;
            $cb = $setB[$k] ?? 0;
            $inter += min($ca, $cb);
            $union += max($ca, $cb);
        }

        return $union > 0 ? ($inter / $union) : 0.0;
    }

    private function padForTrigram(string $s): string
    {
        $s = $this->norm($s);
        return '  ' . $s . '  ';
    }

    private function trigrams(string $s): array
    {
        $len = mb_strlen($s);
        if ($len < 3) return [];

        $out = [];
        for ($i = 0; $i <= $len - 3; $i++) {
            $out[] = mb_substr($s, $i, 3);
        }
        return $out;
    }
}