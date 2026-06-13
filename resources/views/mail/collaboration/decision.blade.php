{{-- resources/views/mail/collaboration-rejection.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $reference_id = $collaborationRequest->id ?? 'N/A';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update on Collaboration Request - Global Trading</title>
    <style>
        /* Embedded CSS for email clients */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7fa; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .preheader { display: none !important; visibility: hidden; mso-hide: all; font-size: 1px; line-height: 1px; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f7fa; padding-bottom: 40px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-top: 30px; }
        .header { background-color: #00466a; color: #ffffff; padding: 25px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; letter-spacing: 0.5px; }
        .content { padding: 40px 30px; color: #4a4a4a; line-height: 1.6; font-size: 15px; }
        .content p { margin-top: 0; margin-bottom: 20px; }
        .reference-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #00466a; padding: 16px 20px; margin: 0 0 25px 0; border-radius: 4px; font-size: 15px; color: #334155; }
        .btn-container { text-align: center; margin: 40px 0 20px 0; }
        .btn { display: inline-block; padding: 14px 32px; background-color: #005b8f; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px; transition: background-color 0.2s; }
        .signature { margin-top: 40px; border-top: 1px solid #f1f5f9; padding-top: 20px; }
        .footer { background-color: #f8fafc; color: #64748b; padding: 25px 30px; font-size: 12px; text-align: center; line-height: 1.6; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 0 0 10px 0; }
        .footer p:last-child { margin-bottom: 0; }
    </style>
</head>
<body>
    <span class="preheader">An update regarding your recent collaboration proposal to Global Trading.</span>

    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Global Trading</h1>
            </div>

            <div class="content">
                <p>Thank you for your interest in partnering with Global Trading and for taking the time to submit your proposal.</p>

                <div class="reference-box">
                    <strong>Reference ID:</strong> {{ $reference_id }}
                </div>

                <p>Following a thorough review by our team, we regret to inform you that we will not be moving forward with this specific collaboration opportunity at this time.</p>

                <p>Due to the high volume of proposals we receive, we are unable to provide individualized feedback on our evaluation process. However, we sincerely appreciate the effort and detail you invested in presenting your business to us.</p>

                <p>Please note that this decision applies strictly to the proposal referenced above. It does not impact any other ongoing inquiries, transactions, or business discussions you may currently have with our team. We remain entirely open to reviewing future products, services, or partnership opportunities that align with our evolving business requirements.</p>

                <p>We encourage you to stay connected with Global Trading. Should our sourcing requirements shift in a way that matches your offerings, we may reach out regarding future opportunities.</p>

                <div class="btn-container">
                    <a href="{{ config('app.url') }}/en/products" class="btn">Explore Our Products</a>
                </div>

                <div class="signature">
                    <p style="margin: 0;">Kind regards,<br><strong style="color: #00466a;">Global Trading Team</strong></p>
                </div>
            </div>

            <div class="footer">
                <p><strong>This is an automated message — please do not reply directly to this email.</strong></p>
                <p>Your personal data is processed in accordance with our Privacy Policy and applicable data-protection regulations. You are receiving this email because you submitted a request via the Global Trading platform.</p>
            </div>
        </div>
    </div>
</body>
</html>