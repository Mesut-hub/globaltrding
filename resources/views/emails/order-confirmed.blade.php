@include('emails.partials.shell-start')
  @php($customer = $order->customer)
  <p style="display:inline-block;background:#E7F6EC;color:#15803D;font-weight:700;font-size:13px;padding:6px 12px;border-radius:999px;margin:0 0 16px;">{{ __('portal.mail.order_confirmed.badge') }}</p>
  <h2 style="margin:0 0 6px;color:#0F172A;font-size:19px;">{{ __('portal.mail.order_confirmed.heading', ['number' => $order->order_number]) }}</h2>
  <p style="margin:0 0 20px;color:#475569;font-size:13.5px;line-height:1.6;">
    {{ __('portal.mail.order_confirmed.intro', ['name' => $contact->name]) }}
  </p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E4E7EC;border-radius:8px;margin-bottom:20px;">
    <tr><td style="padding:10px 14px;font-size:12.5px;color:#64748B;width:40%;">{{ __('portal.mail.order_confirmed.field_product') }}</td><td style="padding:10px 14px;font-size:13px;font-weight:600;color:#0F172A;">{{ $order->product_name }}</td></tr>
    <tr style="background:#FAFBFC;"><td style="padding:10px 14px;font-size:12.5px;color:#64748B;">{{ __('portal.mail.order_confirmed.field_quantity') }}</td><td style="padding:10px 14px;font-size:13px;color:#0F172A;">{{ rtrim(rtrim((string)$order->quantity,'0'),'.') }} {{ $order->quantity_unit }}</td></tr>
    <tr><td style="padding:10px 14px;font-size:12.5px;color:#64748B;">{{ __('portal.mail.order_confirmed.field_shipping_term') }}</td><td style="padding:10px 14px;font-size:13px;color:#0F172A;">{{ $order->shipping_term ?? '—' }}</td></tr>
    <tr style="background:#FAFBFC;"><td style="padding:10px 14px;font-size:12.5px;color:#64748B;">{{ __('portal.mail.order_confirmed.field_delivery_time') }}</td><td style="padding:10px 14px;font-size:13px;color:#0F172A;">{{ $order->delivery_time ?? '—' }}</td></tr>
  </table>

  <p style="margin:0;color:#475569;font-size:13px;line-height:1.7;">
    {{ __('portal.mail.order_confirmed.commitment') }}
  </p>
@include('emails.partials.shell-end')