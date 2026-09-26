@include('emails.partials.shell-start')
  @php($customer = $order->customer)
  <p style="display:inline-block;background:#E7F6EC;color:#15803D;font-weight:700;font-size:13px;padding:6px 12px;border-radius:999px;margin:0 0 16px;">Order is accepted and registered successfully</p>
  <h2 style="margin:0 0 6px;color:#0F172A;font-size:19px;">Order {{ $order->order_number }}</h2>
  <p style="margin:0 0 20px;color:#475569;font-size:13.5px;line-height:1.6;">
    Hello {{ $contact->name }}, we've registered your order and it now enters production and logistics planning.
  </p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E4E7EC;border-radius:8px;margin-bottom:20px;">
    <tr><td style="padding:10px 14px;font-size:12.5px;color:#64748B;width:40%;">Product</td><td style="padding:10px 14px;font-size:13px;font-weight:600;color:#0F172A;">{{ $order->product_name }}</td></tr>
    <tr style="background:#FAFBFC;"><td style="padding:10px 14px;font-size:12.5px;color:#64748B;">Quantity</td><td style="padding:10px 14px;font-size:13px;color:#0F172A;">{{ rtrim(rtrim((string)$order->quantity,'0'),'.') }} {{ $order->quantity_unit }}</td></tr>
    <tr><td style="padding:10px 14px;font-size:12.5px;color:#64748B;">Shipping term</td><td style="padding:10px 14px;font-size:13px;color:#0F172A;">{{ $order->shipping_term ?? '—' }}</td></tr>
    <tr style="background:#FAFBFC;"><td style="padding:10px 14px;font-size:12.5px;color:#64748B;">Delivery time</td><td style="padding:10px 14px;font-size:13px;color:#0F172A;">{{ $order->delivery_time ?? '—' }}</td></tr>
  </table>

  <p style="margin:0;color:#475569;font-size:13px;line-height:1.7;">
    Your business matters to us, and we take the accuracy of every shipment as seriously as you do. Our team will keep this order's status updated at every stage, from loading through final delivery — you can follow it any time from your customer portal, and we'll notify you directly at each significant milestone.
  </p>
@include('emails.partials.shell-end')