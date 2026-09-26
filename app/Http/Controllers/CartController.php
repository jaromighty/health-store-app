<?php

// app/Http/Controllers/CartController.php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreInventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $cartItems = CartItem::where('user_id', $user->id)
            ->with(['product', 'store'])
            ->get();

        // Calculate totals
        $subtotal = $cartItems->sum(fn ($item) => $item->price_at_add * $item->quantity);

        return Inertia::render('cart/index', [
            'cartItems' => $cartItems->map(fn ($item) => [
                'id' => $item->id,
                'product' => $item->product,
                'store' => $item->store,
                'quantity' => $item->quantity,
                'price' => $item->price_at_add,
                'total' => $item->price_at_add * $item->quantity,
                'available_stock' => StoreInventory::where('store_id', $item->store_id)
                    ->where('product_id', $item->product_id)
                    ->value('quantity_in_stock'), // Simplified for MVP
            ]),
            'subtotal' => $subtotal,
            'selectedStore' => $cartItems->first()?->store, // Assume all items are from one store for MVP
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $store = Store::findOrFail($validated['store_id']);

        // Check Inventory
        $inventory = StoreInventory::where('store_id', $store->id)
            ->where('product_id', $product->id)
            ->first();

        if (! $inventory || $inventory->available_quantity < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Not enough stock available at this store.']);
        }

        // Add or Update Cart Item
        CartItem::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'store_id' => $store->id,
                'product_id' => $product->id,
            ],
            [
                'quantity' => DB::raw('quantity + '.$validated['quantity']),
                'price_at_add' => $product->price,
            ]
        );

        return redirect()->back()->with('success', 'Item added to cart.');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $cartItem = CartItem::where('user_id', Auth::id())->findOrFail($id);
        $inventory = StoreInventory::where('store_id', $cartItem->store_id)
            ->where('product_id', $cartItem->product_id)
            ->first();

        if ($validated['quantity'] > 0 && $inventory) {
            if ($inventory->available_quantity < $validated['quantity']) {
                return back()->withErrors(['quantity' => 'Requested quantity exceeds available stock.']);
            }
        }

        if ($validated['quantity'] === 0) {
            $cartItem->delete();
        } else {
            $cartItem->update(['quantity' => $validated['quantity']]);
        }

        return redirect()->back();
    }

    public function remove($id): RedirectResponse
    {
        $cartItem = CartItem::where('user_id', Auth::id())->findOrFail($id);
        $cartItem->delete();

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    public function stores(): Response
    {
        $stores = Store::where('is_active', true)->get(['id', 'name', 'city', 'state', 'address']);

        return Inertia::render('stores/list', ['stores' => $stores]);
    }
}
