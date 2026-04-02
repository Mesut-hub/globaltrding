<?php

namespace App\Http\Controllers;

use App\Models\HomeSection;
use App\Models\NewsPost;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, string $locale)
    {
        $homeSections = HomeSection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Keep news dynamic (as you already do)
        $news = NewsPost::query()
            ->where('is_published', true)
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('home', compact('homeSections', 'news'));
    }
}