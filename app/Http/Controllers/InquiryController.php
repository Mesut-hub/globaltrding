<?php

namespace App\Http\Controllers;

use App\Jobs\SendRequestMailsJob;
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

        $inquiry = InquiryRequest::create([
            ...$data,
            'status' => 'pending',
        ]);

        $productsUrl = (string) config('departments.products_url');

        SendRequestMailsJob::dispatch(
            'inquiry',
            [
                'reference_id' => $inquiry->id,
                'full_name' => $inquiry->full_name,
                'email' => $inquiry->email,
                'company' => $inquiry->company,
                'phone' => $inquiry->phone,
                'subject' => $inquiry->subject,
                'message' => $inquiry->message,
                'created_at' => optional($inquiry->created_at)->toDateTimeString(),
            ],
            (string) $inquiry->email,
            [
                'reference_id' => $inquiry->id,
                'subject' => (string) $inquiry->subject,
                'message' => (string) $inquiry->message,
                'name' => (string) $inquiry->full_name,
                'email' => (string) $inquiry->email,
                'products_url' => $productsUrl,
            ]
        );

        return redirect("/{$locale}/inquiry")
            ->with('success', __('Your inquiry has been submitted. We will contact you soon.'));
    }
}