<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    // Email routing per form type
    private array $routing = [
        'rfq'         => ['to' => 'inquiry@globaltrding.com',         'subject' => 'New RFQ Submission'],
        'technical'   => ['to' => 'info@globaltrding.com',        'subject' => 'Technical Enquiry'],
        'partnership' => ['to' => 'collaboration@globaltrding.com', 'subject' => 'Partnership Enquiry'],
        'media'       => ['to' => 'info@globaltrding.com',        'subject' => 'Press & Media Enquiry'],
        'other'       => ['to' => 'info@globaltrding.com',        'subject' => 'General Enquiry'],
    ];

    public function submit(Request $request): JsonResponse
    {
        $formType = $request->input('form_type', 'other');

        // Validate common required fields
        $validator = Validator::make($request->all(), [
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:180'],
            'form_type' => ['required', 'in:rfq,technical,partnership,media,other'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Additional validation per form type
        $extraRules = match($formType) {
            'rfq' => [
                'company'       => ['required', 'string', 'max:180'],
                'country'       => ['required', 'string', 'max:100'],
                'category'      => ['required', 'string', 'max:120'],
                'specification' => ['required', 'string', 'min:20'],
            ],
            'technical' => [
                'product'  => ['required', 'string', 'max:180'],
                'question' => ['required', 'string', 'min:20'],
            ],
            'partnership' => [
                'company' => ['required', 'string', 'max:180'],
                'country' => ['required', 'string', 'max:100'],
                'type'    => ['required', 'string', 'max:120'],
                'message' => ['required', 'string', 'min:20'],
            ],
            'media' => [
                'publication' => ['required', 'string', 'max:180'],
                'request'     => ['required', 'string', 'min:20'],
            ],
            'other' => [
                'subject' => ['required', 'string', 'max:200'],
                'message' => ['required', 'string', 'min:10'],
            ],
            default => [],
        };

        if (!empty($extraRules)) {
            $extra = Validator::make($request->all(), $extraRules);
            if ($extra->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $extra->errors(),
                ], 422);
            }
        }

        $route  = $this->routing[$formType] ?? $this->routing['other'];
        $data   = $request->except(['_token', '_method']);
        $sender = $request->input('name') . ' <' . $request->input('email') . '>';

        // Send notification to Global Trading
        Mail::send([], [], function ($mail) use ($data, $route, $sender) {
            $mail->to($route['to'])
                 ->replyTo($data['email'], $data['name'])
                 ->subject($route['subject'] . ' — ' . ($data['company'] ?? $data['name']))
                 ->html(view('emails.contact-notification', ['data' => $data])->render());
        });

        // Send auto-acknowledgement to submitter
        Mail::send([], [], function ($mail) use ($data, $formType) {
            $mail->to($data['email'], $data['name'])
                 ->subject('Your enquiry has been received — Global Trading Ltd. Co.')
                 ->html(view('emails.contact-acknowledgement', [
                     'name'     => $data['name'],
                     'formType' => $formType,
                 ])->render());
        });

        return response()->json(['success' => true], 200);
    }

    public function showPage(Request $request, string $locale)
    {
        return view('pages.contact');
    }
}