<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:shopping,ready_for_pickup,out_for_delivery,completed,cancelled',
        ]);

        try {
            $order->transitionTo($validated['status']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        // TODO: fire OrderStatusChanged event -> listener sends email/SMS

        return back()->with('success', "Order {$order->number} is now {$validated['status']}.");
    }
}
