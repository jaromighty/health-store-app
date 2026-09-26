<?php

use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FulfillmentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->name('products.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/stores', [CartController::class, 'stores'])->name('stores.list');

    Route::get('/checkout/fulfillment', [FulfillmentController::class, 'show'])->name('checkout.fulfillment');
    Route::post('/checkout/fulfillment/quote', [FulfillmentController::class, 'quote'])->name('checkout.quote');
    Route::post('/checkout/fulfillment', [FulfillmentController::class, 'store'])->name('checkout.fulfillment.store');

    Route::get('/checkout/review', [CheckoutController::class, 'review'])->name('checkout.review');
    Route::post('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Admin (replace with role middleware when you build roles)
    Route::prefix('admin/orders/{order}')->middleware('can:manage-orders')->group(function () {
        Route::patch('/status', [OrderStatusController::class, 'update'])->name('admin.orders.status');
    });
});

require __DIR__.'/settings.php';
