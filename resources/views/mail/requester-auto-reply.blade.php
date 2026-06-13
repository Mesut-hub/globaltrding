{{-- resources/views/mail/requester-auto-reply.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    @php
        $products_url = config('app.url') . '/en/products';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Request - Global Trading</title>
    <style>
        /* Embedded CSS for email clients */
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7fa; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f7fa; padding-bottom: 40px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px; }
        .header { background-color: #00466a; color: #ffffff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: normal; }
        .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
        .details-box { background-color: #f8f9fa; border-left: 4px solid #00466a; padding: 20px; margin: 25px 0; border-radius: 0 4px 4px 0; }
        .details-box ul { list-style-type: none; padding: 0; margin: 0; }
        .details-box li { margin-bottom: 10px; font-size: 15px; }
        .details-box li strong { color: #555555; display: inline-block; width: 120px; }
        .btn-container { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 14px 28px; background-color: #28a745; color: #ffffff !important; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { background-color: #eeeeee; color: #777777; padding: 20px; font-size: 12px; text-align: center; line-height: 1.5; border-top: 1px solid #dddddd; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Global Trading</h1>
            </div>

            <div class="content">
                <p>Thank you for contacting Global Trading. We have received your request.</p>

                <div class="details-box">
                    <ul>
                        <li><strong>Reference ID:</strong> {{ $reference_id }}</li>
                        <li><strong>Subject:</strong> {{ $subject }}</li>
                        <li><strong>Submitted by:</strong> {{ $name }} (<a href="mailto:{{ $email }}" style="color: #00466a;">{{ $email }}</a>)</li>
                        <li style="margin-top: 15px;"><strong>Message:</strong><br>
                            {{-- white-space: pre-line ensures any line breaks in the user's message are preserved --}}
                            <span style="white-space: pre-line; display: block; margin-top: 5px; color: #555;">{{ $body }}</span>
                        </li>
                    </ul>
                </div>

                <p>Your request has been forwarded to the related department for review. We will get in touch with you shortly.</p>

                <p>In the meantime, you can review our products and find what else you need:</p>
                
                <div class="btn-container">
                    <a href="{{ $products_url }}" class="btn">Browse Products</a>
                </div>

                <p>Thank you,<br><strong>Global Trading Team</strong></p>
            </div>

            <div class="footer">
                <p style="margin-bottom: 10px;"><strong>This is an automated email — please do not reply directly.</strong></p>
                <p style="margin: 0;">Your personal data is processed in accordance with our Privacy Policy and applicable data-protection regulations. It is used solely to handle your request and will not be shared with third parties without your consent.</p>
            </div>
        </div>
    </div>
</body>
</html>