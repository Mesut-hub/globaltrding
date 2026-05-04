<?php

namespace App\Http\Controllers;

use App\Models\InquiryRequest;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function create(string $locale)
    {
        return view('inquiry.create');
    }

    public function store(Request $request, string $locale)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255'],
            'company'   => ['required', 'string', 'max:255'],
            'phone'     => ['required', 'string', 'max:50'],
            'subject'   => ['required', 'string', 'max:255'],
            'message'   => ['required', 'string', 'max:5000'],
        ]);

        InquiryRequest::create([
            ...$data,
            'status' => 'pending',
        ]);

        return redirect("/{$locale}/inquiry")
            ->with('success', 'Your inquiry has been submitted. We will contact you soon.');
    }
}