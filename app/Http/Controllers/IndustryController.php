<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use Illuminate\Http\Request;
use App\Models\Page;

class IndustryController extends Controller
{
    public function index(Request $request, string $locale)
    {
        $industries = Industry::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $page = Page::where('slug', 'industries')
            ->where('is_published', true)
            ->first();

        return view('industries.index', compact('industries', 'page'));
    }

    public function show(Request $request, string $locale, Industry $industry)
    {
        abort_unless($industry->is_published, 404);

        return view('industries.show', compact('industry'));
    }
}