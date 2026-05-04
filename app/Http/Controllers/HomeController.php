<?php

namespace App\Http\Controllers;

use App\Models\HomeSection;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, string $locale)
    {
        $homeSections = HomeSection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('home', compact('homeSections'));
    }
}