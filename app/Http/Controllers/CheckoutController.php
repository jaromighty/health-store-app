<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly PaymentGateway $gateway,
    ) {}

    /**
     * GET /checkout/review — convert cart to pending_payment order & show it.
     */
    public function review()
    {
        $user = Auth::user();

        // Reuse a pending order if the user refreshes; don't stack them
        $order = Order::where('user_id', $user->id)
            ->where('status', 'pending_payment')
            ->first();

        if (! $order) {
            $order = $this->orders->createFromCart($user);
        }

        return Inertia::render('Checkout/Review', [
            'order' => $order->load(['items', 'store', 'fulfillment']),
            'paymentInit' => $this->gateway->initialize($order),
        ]);
    }

    /**
     * POST /checkout/pay — confirm the payment.
     */
    public function pay(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payload' => 'nullable|array',
        ]);

        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending_payment')
            ->findOrFail($validated['order_id']);

        $result = $this->gateway->confirm($order, $validated['payload'] ?? []);

        if (! $result->success) {
            return back()->withErrors(['payment' => $result->error ?? 'Payment failed.']);
        }

        $order->update(['payment_gateway_ref' => $result->reference]);
        $this->orders->confirmPaid($order);

        return redirect()->route('orders.show', $order)->with('success', 'Order placed!');
    }
}
