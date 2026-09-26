import OrderStatusBadge from '@/components/order-status-badge';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { ReviewPageProps } from '@/types';
import {pay, review} from "@/routes/checkout";

export default function Review({ order, paymentInit }: ReviewPageProps) {
    const formatPrice = (n: number) =>
        new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n);

    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const placeOrder = () => {
        setProcessing(true);
        setError(null);

        // When Stripe arrives, this callback is where you mount
        // the Stripe Elements widget using paymentInit.client_secret,
        // collect the payment method, and pass its handle as `payload`.
        router.post(pay(), {
            order_id: order.id,
            payload: { client_secret: paymentInit.client_secret },
        }, {
            onFinish: () => setProcessing(false),
            onError: (errs) => {
                setError(errs.payment as string ?? 'Something went wrong. Please try again.');
            },
        });
    };

    return (
        <>
            <Head title="Review Order" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {/* Fulfillment summary */}
                    <div className="bg-white rounded-lg shadow-sm p-6">
                        <div className="flex justify-between items-start">
                            <div>
                                <h3 className="font-semibold text-gray-900">
                                    {order.fulfillment?.type === 'delivery' ? 'Home Delivery' : 'Store Pickup'}
                                </h3>
                                <p className="text-sm text-gray-500 mt-1">{order.fulfillment?.scheduled_window}</p>
                                <p className="text-sm text-gray-500">
                                    From {order.store.name} — {order.store.city}, {order.store.state}
                                </p>
                            </div>
                            <OrderStatusBadge status={order.status} />
                        </div>
                    </div>

                    {/* Items */}
                    <div className="bg-white rounded-lg shadow-sm divide-y divide-gray-100">
                        {order.items.map(item => (
                            <div key={item.id} className="flex justify-between p-4">
                                <div>
                                    <p className="text-sm font-medium text-gray-900">{item.name}</p>
                                    <p className="text-sm text-gray-500">
                                        {item.quantity} × {formatPrice(item.unit_price)}
                                    </p>
                                </div>
                                <p className="text-sm font-medium text-gray-900">{formatPrice(item.line_total)}</p>
                            </div>
                        ))}
                    </div>

                    {/* Totals */}
                    <div className="bg-white rounded-lg shadow-sm p-6 space-y-2">
                        <div className="flex justify-between text-sm"><span>Subtotal</span><span>{formatPrice(order.subtotal)}</span></div>
                        <div className="flex justify-between text-sm">
                            <span>{order.fulfillment?.type === 'delivery' ? 'Delivery' : 'Pickup'}</span>
                            <span>{order.fulfillment_fee === 0 ? 'FREE' : formatPrice(order.fulfillment_fee)}</span>
                        </div>
                        {order.tip > 0 && (
                            <div className="flex justify-between text-sm"><span>Driver Tip</span><span>{formatPrice(order.tip)}</span></div>
                        )}
                        <div className="flex justify-between text-sm"><span>Tax</span><span>{formatPrice(order.tax)}</span></div>
                        <div className="flex justify-between font-semibold text-gray-900 pt-2 border-t border-gray-200">
                            <span>Total</span><span>{formatPrice(order.total)}</span>
                        </div>
                    </div>

                    {/* Payment */}
                    <div className="bg-white rounded-lg shadow-sm p-6">
                        <h3 className="font-semibold text-gray-900 mb-4">Payment</h3>

                        {paymentInit.provider === 'fake' && (
                            <p className="text-xs text-gray-400 mb-3">
                                Simulated payments (fake gateway) — no real charge will occur.
                            </p>
                        )}
                        {/* Future: <StripeElements clientSecret={paymentInit.client_secret} /> mounts here */}

                        {error && <div className="mb-3 bg-red-50 border border-red-200 text-red-700 rounded-md p-3 text-sm">{error}</div>}

                        <button
                            onClick={placeOrder}
                            disabled={processing}
                            className="w-full bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white font-medium py-3 rounded-md"
                        >
                            {processing ? 'Processing payment…' : `Pay ${formatPrice(order.total)}`}
                        </button>
                        <p className="text-center text-xs text-gray-400 mt-2">Order {order.number}</p>
                    </div>
                </div>
            </div>
        </>
    );
}

Review.layout = {
    breadcrumbs: [
        {
            title: 'Review Your Order',
            href: review(),
        }
    ]
}
