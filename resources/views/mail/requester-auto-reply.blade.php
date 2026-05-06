{{-- resources/views/mail/requester-auto-reply.blade.php --}}
Thank you for contacting Global Trading for your request.

Your request details:
  • Reference ID : {{ $reference_id }}
  • Subject      : {{ $subject }}
  • Message      : {{ $message }}
  • Submitted by : {{ $name }} ({{ $email }})

Your request has been forwarded to the related department for review.
We will get in touch with you shortly.

You can review our products and find what else you need:
[Browse Products] → {{ $products_url }}

Thank you,
Global Trading Team

─────────────────────────────────────────────────────────────
This is an automated email — please do not reply directly.
Your personal data is processed in accordance with our Privacy
Policy and applicable data-protection regulations. It is used
solely to handle your request and will not be shared with third
parties without your consent.
─────────────────────────────────────────────────────────────