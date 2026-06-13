<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request, string $locale)
    {
        $news = NewsPost::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('news.index', compact('news'));
    }

    public function show(Request $request, string $locale, string $slug)
    {
        $post = NewsPost::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('news.show', compact('post'));
    }
}