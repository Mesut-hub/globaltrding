<?php

namespace App\Http\Controllers;

use App\Mail\CollaborationReceivedMail;
use App\Models\CollaborationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CollaborationController extends Controller
{
    public function create(string $locale)
    {
        return view('collaboration.create');
    }

    public function store(Request $request, string $locale)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $collab = CollaborationRequest::create([
            ...$data,
            'status' => 'pending',
        ]);

        // Applicant confirmation
        Mail::to($collab->email)->send(new CollaborationReceivedMail($collab));

        // Admin notification (log mailer for now)
        $adminEmail = config('mail.from.address'); // simple default
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new CollaborationReceivedMail($collab, true));
        }

        return redirect("/{$locale}/collaboration")
            ->with('success', 'Your request has been submitted. We will contact you soon.');
    }
}