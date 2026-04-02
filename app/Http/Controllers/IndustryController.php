<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use Illuminate\Http\Request;

class IndustryController extends Controller
{
    public function index(Request $request, string $locale)
    {
        $industries = Industry::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        return view('industries.index', compact('industries'));
    }

    public function show(Request $request, string $locale, Industry $industry)
    {
        abort_unless($industry->is_published, 404);

        return view('industries.show', compact('industry'));
    }
}