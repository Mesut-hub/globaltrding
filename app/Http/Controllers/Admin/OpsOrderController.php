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
        $orders = $customer->orders()->with(['responsiblePersons', 'statusUpdates'])->latest('order_date')->get();
        $tab = $request->query('tab', 'orders');

        $selectedOrder = $request->query('order')
            ? $orders->firstWhere('id', (int) $request->query('order'))
            : $orders->firstWhere('status', Order::STATUS_SENT);

        $editingOrder = $request->filled('edit_order')
            ? $orders->firstWhere('id', (int) $request->query('edit_order'))
            : null;

        $editingStatus = null;
        if ($request->filled('edit_status') && $selectedOrder) {
            $editingStatus = $selectedOrder->statusUpdates->firstWhere('id', (int) $request->query('edit_status'));
        }

        return view('ops.orders.company', [
            'customer' => $customer,
            'orders' => $orders,
            'tab' => $tab,
            'showAddForm' => $request->boolean('new') || $editingOrder !== null,
            'editingOrder' => $editingOrder,
            'selectedOrder' => $selectedOrder,
            'editingStatus' => $editingStatus,
            'stageOptions' => OrderStatusStage::options(),
        ]);
    }

    private function orderRules(): array
    {
        return [
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
        ];
    }

    private function emailOrderConfirmation(Order $order): void
    {
        $recipients = $order->responsiblePersons()->where('receives_notifications', true)->whereNotNull('email')->get();
        foreach ($recipients as $contact) {
            Mail::to($contact->email)->send(new OrderConfirmedMail($order, $contact));
        }
        $order->update(['status' => Order::STATUS_SENT, 'sent_at' => now()]);
    }

    public function storeOrder(Request $request, Customer $customer)
    {
        $data = $request->validate($this->orderRules());

        $order = $customer->orders()->create([
            ...collect($data)->except(['responsible_persons', 'action'])->toArray(),
            'is_contracted' => $request->boolean('is_contracted'),
            'status' => Order::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);

        $order->responsiblePersons()->sync($data['responsible_persons'] ?? []);

        if ($data['action'] === 'send') {
            $this->emailOrderConfirmation($order);
        }

        return redirect()->route('ops.orders.company', $customer)->with('status', 'Order saved.');
    }

    public function updateOrder(Request $request, Customer $customer, Order $order)
    {
        $data = $request->validate($this->orderRules());

        $order->update([
            ...collect($data)->except(['responsible_persons', 'action'])->toArray(),
            'is_contracted' => $request->boolean('is_contracted'),
        ]);
        $order->responsiblePersons()->sync($data['responsible_persons'] ?? []);

        if ($data['action'] === 'send' && $order->status === Order::STATUS_DRAFT) {
            $this->emailOrderConfirmation($order);
        }

        return redirect()->route('ops.orders.company', $customer)->with('status', 'Order updated.');
    }

    public function sendOrder(Customer $customer, Order $order)
    {
        $this->emailOrderConfirmation($order);

        return back()->with('status', 'Order sent.');
    }

    private function statusRules(): array
    {
        return [
            'stage_key' => ['required', 'string'],
            'custom_label' => ['nullable', 'string', 'max:255'],
            'stage_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function resolveStage(array $data): array
    {
        if ($data['stage_key'] === 'custom') {
            $label = $data['custom_label'] ?: 'Custom stage';
            return [Str::slug($label, '_'), $label];
        }

        return [$data['stage_key'], OrderStatusStage::from($data['stage_key'])->label()];
    }

    public function storeStatus(Request $request, Customer $customer, Order $order)
    {
        $data = $request->validate($this->statusRules());
        [$key, $label] = $this->resolveStage($data);

        $update = OrderStatusUpdate::create([
            'order_id' => $order->id,
            'stage_key' => $key,
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

        return redirect()->route('ops.orders.company', ['customer' => $customer, 'tab' => 'updates', 'order' => $order->id])
            ->with('status', 'Cargo status updated.');
    }

    public function updateStatus(Request $request, Customer $customer, Order $order, OrderStatusUpdate $update)
    {
        $data = $request->validate($this->statusRules());
        [$key, $label] = $this->resolveStage($data);

        $update->update([
            'stage_key' => $key,
            'stage_label' => $label,
            'stage_date' => $data['stage_date'],
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('ops.orders.company', ['customer' => $customer, 'tab' => 'updates', 'order' => $order->id])
            ->with('status', 'Status entry updated.');
    }

    public function resendStatus(Customer $customer, Order $order, OrderStatusUpdate $update)
    {
        $recipients = $order->responsiblePersons()->where('receives_notifications', true)->whereNotNull('email')->get();
        foreach ($recipients as $contact) {
            Mail::to($contact->email)->send(new OrderStatusUpdateMail($order, $update, $contact));
        }
        $order->update(['status_last_sent_at' => now()]);

        return back()->with('status', 'Status update re-sent.');
    }

    public function deleteStatus(Customer $customer, Order $order, OrderStatusUpdate $update)
    {
        $update->delete();

        return redirect()->route('ops.orders.company', ['customer' => $customer, 'tab' => 'updates', 'order' => $order->id])
            ->with('status', 'Status entry removed.');
    }
}