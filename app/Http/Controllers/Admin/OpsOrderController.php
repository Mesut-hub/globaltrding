<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatusStage;
use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmedMail;
use App\Mail\OrderStatusUpdateMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OpsOrderController extends Controller
{
    public function companies()
    {
        $customers = Customer::withCount([
            'orders as open_orders_count' => fn ($q) => $q->where('status', Order::STATUS_SENT),
            'orders as in_transit_count' => fn ($q) => $q->where('status', Order::STATUS_SENT)
                ->whereDoesntHave('statusUpdates', fn ($q2) => $q2->where('stage_key', OrderStatusStage::DELIVERED->value)),
        ])->orderBy('company_name')->get();

        return view('ops.orders.companies', compact('customers'));
    }

    public function company(Request $request, Customer $customer)
    {
        $orders = $customer->orders()->with('responsiblePersons')->latest('order_date')->get();
        $tab = $request->query('tab', 'orders');
        $selectedOrder = $request->query('order')
            ? $orders->firstWhere('id', (int) $request->query('order'))
            : $orders->firstWhere('status', Order::STATUS_SENT);

        return view('ops.orders.company', [
            'customer' => $customer,
            'orders' => $orders,
            'tab' => $tab,
            'showAddForm' => $request->boolean('new'),
            'selectedOrder' => $selectedOrder,
            'stageOptions' => OrderStatusStage::options(),
        ]);
    }

    public function storeOrder(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:100'],
            'order_date' => ['nullable', 'date'],
            'delivered_date' => ['nullable', 'date'],
            'balanced_finished_date' => ['nullable', 'date'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'payment_term' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric'],
            'quantity_unit' => ['nullable', 'string', 'max:20'],
            'delivery_time' => ['nullable', 'string', 'max:255'],
            'shipping_term' => ['nullable', 'string', 'max:100'],
            'delivery_point' => ['nullable', 'string', 'max:100'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'responsible_persons' => ['array'],
            'responsible_persons.*' => ['integer', 'exists:customer_contacts,id'],
            'action' => ['required', 'in:save,send'],
        ]);

        $order = $customer->orders()->create([
            ...collect($data)->except(['responsible_persons', 'action'])->toArray(),
            'is_contracted' => $request->boolean('is_contracted'),
            'status' => Order::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);

        $order->responsiblePersons()->sync($data['responsible_persons'] ?? []);

        if ($data['action'] === 'send') {
            $recipients = $order->responsiblePersons()->where('receives_notifications', true)->whereNotNull('email')->get();
            foreach ($recipients as $contact) {
                Mail::to($contact->email)->send(new OrderConfirmedMail($order, $contact));
            }
            $order->update(['status' => Order::STATUS_SENT, 'sent_at' => now()]);
        }

        return redirect()->route('ops.orders.company', $customer)->with('status', 'Order saved.');
    }

    public function sendOrder(Customer $customer, Order $order)
    {
        $recipients = $order->responsiblePersons()->where('receives_notifications', true)->whereNotNull('email')->get();
        foreach ($recipients as $contact) {
            Mail::to($contact->email)->send(new OrderConfirmedMail($order, $contact));
        }
        $order->update(['status' => Order::STATUS_SENT, 'sent_at' => now()]);

        return back()->with('status', 'Order sent.');
    }

    public function storeStatus(Request $request, Customer $customer, Order $order)
    {
        $data = $request->validate([
            'stage_key' => ['required', 'string'],
            'custom_label' => ['nullable', 'string', 'max:255'],
            'stage_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $label = $data['stage_key'] === 'custom' ? $data['custom_label'] : OrderStatusStage::from($data['stage_key'])->label();

        $update = OrderStatusUpdate::create([
            'order_id' => $order->id,
            'stage_key' => $data['stage_key'] === 'custom' ? Str::slug($label, '_') : $data['stage_key'],
            'stage_label' => $label,
            'stage_date' => $data['stage_date'],
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if ($request->boolean('send_email')) {
            $recipients = $order->responsiblePersons()->where('receives_notifications', true)->whereNotNull('email')->get();
            foreach ($recipients as $contact) {
                Mail::to($contact->email)->send(new OrderStatusUpdateMail($order, $update, $contact));
            }
            $order->update(['status_last_sent_at' => now()]);
        }

        return redirect()->route('ops.orders.company', ['customer' => $customer, 'tab' => 'updates', 'order' => $order->id])->with('status', 'Cargo status updated.');
    }
}