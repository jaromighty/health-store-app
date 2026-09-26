<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Fulfillment;
use App\Models\Store;
use App\Models\TimeSlot;
use App\Services\FulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FulfillmentController extends Controller
{
    public function __construct(private readonly FulfillmentService $fulfillment) {}

    /**
     * Step 1: Choose fulfillment mode + address validation + slots.
     * GET /checkout/fulfillment
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();
        $cartItems = CartItem::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $storeId = $request->session()->get('selected_store_id', $cartItems->first()->store_id);
        $store = Store::findOrFail($storeId);
        $type = $request->input('type', 'pickup'); // Pickup-first, as we planned

        // Validate delivery zip if provided
        $zipError = null;
        if ($type === 'delivery' && $request->filled('zip')) {
            if (! $this->fulfillment->isZipServiced($storeId, $request->input('zip'))) {
                $zipError = "We're sorry, we don't deliver to {$request->input('zip')} from this store yet.";
            }
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->price_at_add * $item->quantity);

        return Inertia::render('Checkout/Fulfillment', [
            'store' => $store->only(['id', 'name', 'city', 'state', 'address']),
            'type' => $type,
            'zip' => $request->input('zip'),
            'zipError' => $zipError,
            'slotsByDate' => ($type === 'delivery' && $zipError)
                ? []
                : $this->fulfillment->getAvailableSlots($storeId, $type),
            'subtotal' => $subtotal,
            'fees' => $this->fulfillment->calculateFee($type, $subtotal),
        ]);
    }

    /**
     * Quick fee recalculation endpoint (called when toggling mode or adding tip).
     * POST /checkout/fulfillment/quote
     */
    public function quote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:delivery,pickup',
            'subtotal' => 'required|numeric|min:0',
            'tip' => 'nullable|numeric|min:0|max:100',
        ]);

        return response()->json(
            $this->fulfillment->calculateFee($validated['type'], $validated['subtotal'], $validated['tip'] ?? null)
        );
    }

    /**
     * Step 2: Save the fulfillment selection.
     * POST /checkout/fulfillment
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:delivery,pickup',
            'time_slot_id' => 'required|exists:time_slots,id',
            'slot_date' => 'required|date|after_or_equal:today',
            // Conditional delivery validation — this is the "mode-aware" logic
            ...($request->input('type') === 'delivery' ? [
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'state' => 'required|string|size:2',
                'zip' => 'required|string|max:10',
            ] : [
                'vehicle_description' => 'nullable|string|max:100',
            ]),
            'tip' => 'nullable|numeric|min:0|max:100',
        ]);

        $user = Auth::user();
        $storeId = $request->session()->get('selected_store_id',
            CartItem::where('user_id', $user->id)->value('store_id'));

        $slot = TimeSlot::where('store_id', $storeId)
            ->where('fulfillment_type', $validated['type'])
            ->where('is_active', true)
            ->findOrFail($validated['time_slot_id']);

        // Geofence check for delivery
        if ($validated['type'] === 'delivery') {
            if (! $this->fulfillment->isZipServiced($storeId, $validated['zip'])) {
                return back()->withErrors(['zip' => 'This zip code is outside our delivery area for this store.']);
            }
        }

        $cartItems = CartItem::where('user_id', $user->id)->get();
        $subtotal = $cartItems->sum(fn ($item) => $item->price_at_add * $item->quantity);
        $fees = $this->fulfillment->calculateFee($validated['type'], $subtotal, $validated['tip'] ?? null);

        // Reserve the slot
        $booking = $this->fulfillment->reserveSlot($slot, $validated['slot_date']);

        // Save fulfillment (one active per user — upsert)
        Fulfillment::updateOrCreate(
            ['user_id' => $user->id, 'status' => 'scheduled'],
            [
                'store_id' => $storeId,
                'slot_booking_id' => $booking->id,
                'type' => $validated['type'],
                'delivery_address' => $validated['address'] ?? null,
                'delivery_city' => $validated['city'] ?? null,
                'delivery_state' => $validated['state'] ?? null,
                'delivery_zip' => $validated['zip'] ?? null,
                'vehicle_description' => $validated['vehicle_description'] ?? null,
                'fee' => $fees['fee'],
                'tip' => $fees['tip'],
            ]
        );

        $booking->increment('booked_count');

        return redirect()->route('checkout.review')->with('success', 'Fulfillment scheduled!');
    }
}
