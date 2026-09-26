<?php

namespace App\Services\Payments;

use App\Models\Order;

/**
 * Fake gateway for development. Auto-confirms payments and generates
 * deterministic references. Swap for StripeGateway when ready:
 *
 *   // bootstrap/app.php (or AppServiceProvider)
 *   $this->app->bind(PaymentGateway::class, fn () => match (config('payments.gateway')) {
 *       'stripe' => new StripeGateway(config('services.stripe.secret')),
 *       default  => new FakeGateway(),
 *   });
 */
class FakeGateway implements PaymentGateway
{
    public function initialize(Order $order): array
    {
        return [
            'provider' => 'fake',
            // When Stripe arrives, this becomes:
            // 'client_secret' => $intent->client_secret
            'client_secret' => 'fake_secret_'.$order->number,
        ];
    }

    public function confirm(Order $order, array $payload = []): PaymentResult
    {
        // Simulated: succeed unless the payload contains { force_fail: true }
        if (($payload['force_fail'] ?? false) === true) {
            return PaymentResult::failure('Your card was declined. (simulated)');
        }

        return PaymentResult::success('fake_pay_'.$order->number.'_'.now()->timestamp);
    }

    public function refund(Order $order): PaymentResult
    {
        return PaymentResult::success('fake_refund_'.$order->number);
    }
}
