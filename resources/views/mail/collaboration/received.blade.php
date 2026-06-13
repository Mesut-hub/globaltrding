{{-- resources/views/mail/collaboration-approval.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    @php
        $reference_id = $collaborationRequest->id ?? 'N/A';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collaboration Request Approved - Global Trading</title>
    <style>
        /* Embedded CSS for email clients */
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7fa; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f7fa; padding-bottom: 40px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px; }
        .header { background-color: #00466a; color: #ffffff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: normal; }
        .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 15px; }
        .content p { margin-bottom: 16px; }
        /* Using a green accent for the approval box */
        .reference-box { background-color: #f0fdf4; border-left: 4px solid #28a745; padding: 15px 20px; margin: 20px 0; border-radius: 0 4px 4px 0; font-size: 16px; color: #166534; }
        .btn-container { text-align: center; margin: 35px 0; }
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
                <p>Thank you for your interest in collaborating with Global Trading and for submitting your request.</p>

                <div class="reference-box">
                    <strong>Approved:</strong> Collaboration Request {{ $reference_id }}
                </div>

                <p>We are pleased to inform you that, following our review process, your collaboration request has been approved.</p>

                <p>We appreciate the time and effort you invested in presenting your proposal and are delighted to welcome you as a potential business partner. We believe there may be valuable opportunities for cooperation and look forward to exploring them with you.</p>

                <p>Our team may contact you shortly regarding the next steps, additional information requirements, documentation, commercial discussions, or other matters necessary to move the collaboration process forward. Please note that approval of this request does not automatically constitute a binding agreement, purchase commitment, exclusivity arrangement, or contractual relationship unless otherwise agreed in writing by both parties.</p>

                <p>This approval does not affect any other inquiries, quotations, transactions, or business discussions you may currently have with us. Likewise, future opportunities for cooperation may arise as our sourcing requirements, projects, and business activities continue to evolve.</p>

                <p>If you have an account registered with us and have consented to receive communications in accordance with your account preferences and applicable data protection regulations, we may contact you regarding additional opportunities that may be relevant to your business.</p>

                <p>Thank you for your interest in Global Trading and for choosing to engage with us. We look forward to building a productive and mutually beneficial business relationship.</p>

                <div class="btn-container">
                    <a href="{{ config('app.url') }}/en/products" class="btn">Explore Our Products</a>
                </div>

                <p style="margin-top: 30px;">Kind regards,<br><strong>Global Trading Team</strong></p>
            </div>

            <div class="footer">
                <p style="margin-bottom: 10px;"><strong>This is an automated email — please do not reply directly.</strong></p>
                <p style="margin: 0;">Your personal data is processed in accordance with our Privacy Policy and applicable data-protection regulations.</p>
            </div>
        </div>
    </div>
</body>
</html>