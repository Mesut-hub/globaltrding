<?php

namespace App\Http\Controllers;

use App\Jobs\SendRequestMailsJob;
use App\Models\CollaborationRequest;
use Illuminate\Http\Request;

class CollaborationController extends Controller
{
    public function index(string $locale)
    {
        return view('collaboration.index');
    }

    public function create(string $locale)
    {
        return view('collaboration.create');
    }

    public function store(Request $request, string $locale)
    {
        $data = $request->validate([
            'full_name'   => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255'],
            'company'     => ['required', 'string', 'max:255'],
            'vat_number'  => ['required', 'string', 'max:64'],
            'phone'       => ['required', 'string', 'max:50'],
            'country'     => ['required', 'string', 'max:100'],
            'message'     => ['required', 'string', 'max:5000'],
        ]);

        $collab = CollaborationRequest::create([
            ...$data,
            'status' => 'pending',
        ]);

        $productsUrl = (string) config('departments.products_url');

        SendRequestMailsJob::dispatch(
            'collaboration',
            [
                'reference_id' => $collab->id,
                'full_name' => $collab->full_name,
                'email' => $collab->email,
                'company' => $collab->company,
                'vat_number' => $collab->vat_number,
                'phone' => $collab->phone,
                'country' => $collab->country,
                'subject' => 'Collaboration request',
                'message' => $collab->message,
                'created_at' => optional($collab->created_at)->toDateTimeString(),
            ],
            (string) $collab->email,
            [
                'reference_id' => $collab->id,
                'subject' => 'Collaboration request',
                'body' => (string) $collab->message,
                'name' => (string) $collab->full_name,
                'email' => (string) $collab->email,
                'products_url' => $productsUrl,
            ]
        );

        return redirect("/{$locale}/collaboration")
            ->with('success', __('forms.success_generic'));
    }
}