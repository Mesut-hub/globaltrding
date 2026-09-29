<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class PortalDashboardController extends Controller
{
    private function customer(): Customer
    {
        return Auth::guard('customer')->user();
    }

    public function home(string $locale)
    {
        return view('portal.home', ['customer' => $this->customer()]);
    }

    public function orders(string $locale)
    {
        $orders = $this->customer()->orders()
            ->where('status', \App\Models\Order::STATUS_SENT)
            ->latest('order_date')
            ->get();

        return view('portal.orders.index', ['orders' => $orders]);
    }

    public function orderShow(string $locale, int $order)
    {
        $order = $this->customer()->orders()
            ->where('status', \App\Models\Order::STATUS_SENT)
            ->with('responsiblePersons')
            ->findOrFail($order);

        return view('portal.orders.show', ['order' => $order]);
    }

    public function status(string $locale, ?int $order = null)
    {
        $query = $this->customer()->orders()->where('status', \App\Models\Order::STATUS_SENT);

        $allOrders = (clone $query)->latest('order_date')->get();

        $order = $order
            ? $query->findOrFail($order)
            : $allOrders->first();

        $updates = $order ? $order->statusUpdates()->get() : collect();

        return view('portal.status', ['order' => $order, 'updates' => $updates, 'allOrders' => $allOrders]);
    }
}