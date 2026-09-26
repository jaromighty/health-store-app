<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['store:id,name,city,state', 'fulfillment'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Orders/Index', ['orders' => $orders]);
    }

    public function show(Order $order): Response
    {
        Gate::authorize('view', $order);

        return Inertia::render('orders/show', [
            'order' => $order->load(['items', 'store', 'fulfillment']),
        ]);
    }
}
