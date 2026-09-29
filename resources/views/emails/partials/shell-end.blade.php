</td></tr>
<tr><td style="padding:20px 28px;border-top:1px solid #E4E7EC;background:#FAFBFC;">
  <p style="margin:0;font-size:11.5px;line-height:1.6;color:#8A93A5;">
    {{ __('portal.mail.footer', ['company' => $customer->company_name ?? '']) }}
    <a href="{{ rtrim(config('app.url'),'/') }}/{{ $customer->preferred_locale ?? 'en' }}/pages/privacy-policy" style="color:#8A93A5;">{{ __('portal.mail.privacy_policy') }}</a>
  </p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>