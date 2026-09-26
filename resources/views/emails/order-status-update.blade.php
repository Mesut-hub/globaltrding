@php($isDelivered = $update->stage_key === \App\Enums\OrderStatusStage::DELIVERED->value)
@include('emails.partials.shell-start')
  @php($customer = $order->customer)
  <p style="display:inline-block;background:{{ $isDelivered ? '#E7F6EC' : '#FBF1DD' }};color:{{ $isDelivered ? '#15803D' : '#7A5A1E' }};font-weight:700;font-size:13px;padding:6px 12px;border-radius:999px;margin:0 0 16px;">
    {{ $isDelivered ? 'Load is delivered successfully' : 'Shipment status update' }}
  </p>
  <h2 style="margin:0 0 6px;color:#0F172A;font-size:19px;">Order {{ $order->order_number }}</h2>
  <p style="margin:0 0 20px;color:#475569;font-size:13.5px;line-height:1.6;">Hello {{ $contact->name }}, here's the latest on your shipment.</p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E4E7EC;border-radius:8px;">
    <tr><td style="padding:14px 16px;">
      <p style="margin:0 0 4px;font-size:11.5px;color:#64748B;">{{ $update->stage_date->format('d M Y') }}</p>
      <p style="margin:0;font-size:15px;font-weight:700;color:#0F172A;">{{ $update->stage_label }}</p>
      @if($update->notes)
        <p style="margin:8px 0 0;font-size:13px;color:#475569;">{{ $update->notes }}</p>
      @endif
    </td></tr>
  </table>

  @if($isDelivered)
    <p style="margin:18px 0 0;color:#475569;font-size:13px;line-height:1.7;">Thank you for trading with Global Trading — we look forward to your next order.</p>
  @endif
@include('emails.partials.shell-end')