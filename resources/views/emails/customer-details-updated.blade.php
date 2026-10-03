@include('emails.partials.shell-start')
  <p style="display:inline-block;background:#FBF1DD;color:#7A5A1E;font-weight:700;font-size:13px;padding:6px 12px;border-radius:999px;margin:0 0 16px;">{{ __('portal.mail.details_updated.badge') }}</p>
  <h2 style="margin:0 0 6px;color:#0F172A;font-size:19px;">{{ __('portal.mail.details_updated.heading', ['name' => $contact->name]) }}</h2>
  <p style="margin:0 0 20px;color:#475569;font-size:13.5px;line-height:1.6;">{{ __('portal.mail.details_updated.intro', ['company' => $customer->company_name]) }}</p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E4E7EC;border-radius:8px;">
    <tr><td style="padding:10px 14px;font-size:12.5px;color:#64748B;width:40%;">{{ __('portal.mail.registration.field_id') }}</td><td style="padding:10px 14px;font-size:13px;font-weight:600;color:#0F172A;">{{ $customer->customer_code }}</td></tr>
    <tr style="background:#FAFBFC;"><td style="padding:10px 14px;font-size:12.5px;color:#64748B;">{{ __('portal.mail.registration.field_company') }}</td><td style="padding:10px 14px;font-size:13px;font-weight:600;color:#0F172A;">{{ $customer->company_name }}</td></tr>
    <tr><td style="padding:10px 14px;font-size:12.5px;color:#64748B;">{{ __('portal.mail.registration.field_phone') }}</td><td style="padding:10px 14px;font-size:13px;color:#0F172A;">{{ $customer->phone ?? '—' }}</td></tr>
    <tr style="background:#FAFBFC;"><td style="padding:10px 14px;font-size:12.5px;color:#64748B;">{{ __('portal.mail.registration.field_email') }}</td><td style="padding:10px 14px;font-size:13px;color:#0F172A;">{{ $customer->email }}</td></tr>
  </table>
@include('emails.partials.shell-end')