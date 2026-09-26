<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreInventory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public const float TAX_RATE = 0.0881; // Placeholder CO-ish rate; make configurable per store

    public function createFromCart(User $user): Order
    {
        return DB::transaction(function () use ($user) {
            $cartItems = CartItem::where('user_id', $user->id)
                ->with('product')
                ->lockForUpdate()
                ->get();

            abort_if($cartItems->isEmpty(), 400, 'Cart is empty.');

            $fulfillment = Fulfillment::where('user_id', $user->id)
                ->where('status', 'scheduled')
                ->firstOrFail();

            $subtotal = $cartItems->sum(fn ($i) => $i->price_at_add * $i->quantity);
            $tax = round($subtotal * self::TAX_RATE, 2);
            $total = $subtotal + $fulfillment->fee + $fulfillment->tip + $tax;

            $order = Order::create([
                'user_id' => $user->id,
                'store_id' => $fulfillment->store_id,
                'fulfillment_id' => $fulfillment->id,
                'subtotal' => $subtotal,
                'fulfillment_fee' => $fulfillment->fee,
                'tip' => $fulfillment->tip,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pending_payment',
            ]);

            // Snapshot items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'sku' => $item->product->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price_at_add,
                    'line_total' => $item->price_at_add * $item->quantity,
                ]);
            }

            return $order->fresh(['items', 'store', 'fulfillment']);
        });
    }

    /**
     * Called after successful payment: promote to confirmed, consume stock.
     */
    public function confirmPaid(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'confirmed',
                'paid_at' => now(),
            ]);

            // Consume inventory reservations -> actual stock deduction
            foreach ($order->items as $item) {
                StoreInventory::where('store_id', $order->store_id)
                    ->where('product_id', $item->product_id)
                    ->decrement('quantity_in_stock', $item->quantity);
            }

            // Clear the cart & retire the fulfillment record
            CartItem::where('user_id', $order->user_id)->delete();
            $order->fulfillment?->update(['status' => 'ready']); // staff now sees it
        });
    }
}
