<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * Begin a payment for an order.
     * Returns a client-side reference/handle for completing payment
     * (e.g., a Stripe PaymentIntent client_secret).
     */
    public function initialize(Order $order): array;

    /**
     * Confirm a completed payment using the client-side handle/reference.
     */
    public function confirm(Order $order, array $payload = []): PaymentResult;

    /**
     * Refund a payment (full refund for MVP).
     */
    public function refund(Order $order): PaymentResult;
}
