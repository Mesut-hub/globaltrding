@include('emails.partials.shell-start')
  <h2 style="margin:0 0 6px;color:#0F172A;font-size:19px;">{{ __('portal.mail.password_reset.heading') }}</h2>
  <p style="margin:0 0 20px;color:#475569;font-size:13.5px;line-height:1.6;">{{ __('portal.mail.password_reset.intro', ['name' => $contact->name, 'company' => $customer->company_name]) }}</p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FBF1DD;border:1px solid #EBDBB2;border-radius:8px;">
    <tr><td style="padding:16px 18px;">
      <p style="margin:0;font-size:13.5px;color:#5A4212;">{{ __('portal.mail.password_reset.username') }}: <strong>{{ $customer->username }}</strong><br>{{ __('portal.mail.password_reset.password') }}: <strong>{{ $plainPassword }}</strong></p>
      <p style="margin:10px 0 0;font-size:12px;color:#7A5A1E;">{{ __('portal.mail.password_reset.note') }}</p>
    </td></tr>
  </table>
@include('emails.partials.shell-end')