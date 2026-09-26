</td></tr>
<tr><td style="padding:20px 28px;border-top:1px solid #E4E7EC;background:#FAFBFC;">
  <p style="margin:0;font-size:11.5px;line-height:1.6;color:#8A93A5;">
    This message contains information intended only for the named recipient(s) at {{ $customer->company_name ?? 'your company' }}.
    Global Trading Ltd. Co. processes your data solely to manage this business relationship, in line with our
    <a href="{{ rtrim(config('app.url'),'/') }}/en/pages/privacy-policy" style="color:#8A93A5;">Privacy Policy</a>. If you believe you received this in error, please contact us and delete it.
  </p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>