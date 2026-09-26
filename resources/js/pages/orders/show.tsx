import OrderStatusBadge from '@/components/order-status-badge';
import { Head, router } from '@inertiajs/react';
import { Order } from '@/types';
import {show} from "@/routes/orders";
import {add} from "@/routes/cart";

const TIMELINE: Record<'delivery' | 'pickup', string[]> = {
    pickup: ['confirmed', 'shopping', 'ready_for_pickup', 'completed'],
    delivery: ['confirmed', 'shopping', 'out_for_delivery', 'completed'],
};

export default function Show({ order }: { order: Order }) {
    const formatPrice = (n: number) =>
        new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n);

    const steps = TIMELINE[order.fulfillment?.type ?? 'pickup'];
    const currentIdx = steps.indexOf(order.status);

    const reorder = () => {
        // Backend endpoint would re-add snapshot items to the cart
        // (validating current stock first). Add when we wire it.
        router.post(add(), { /* bulk items */ });
    };

    return (
        <>
            <Head title={`Order ${order.number}`} />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    <div className="flex items-center justify-between">
                        <OrderStatusBadge status={order.status} />
                        <button onClick={reorder} className="text-sm font-medium text-purple-600 hover:text-purple-800">
                            Reorder Items
                        </button>
                    </div>

                    {/* Timeline */}
                    {order.status !== 'cancelled' && (
                        <div className="bg-white rounded-lg shadow-sm p-6">
                            <div className="flex items-center">
                                {steps.map((step, i) => (
                                    <div key={step} className="flex items-center flex-1 last:flex-none">
                                        <div className={`h-3 w-3 rounded-full shrink-0 ${i <= currentIdx ? 'bg-purple-600' : 'bg-gray-200'}`} />
                                        {i < steps.length - 1 && (
                                            <div className={`h-0.5 flex-1 mx-2 ${i < currentIdx ? 'bg-purple-600' : 'bg-gray-200'}`} />
                                        )}
                                    </div>
                                ))}
                            </div>
                            <div className="flex justify-between mt-2 text-xs">
                                {steps.map((step) => (
                                    <span key={step} className="capitalize text-gray-600">{step.replace(/_/g, ' ')}</span>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Items + totals — reuse the same structure as Review.tsx */}
                    <div className="bg-white rounded-lg shadow-sm divide-y divide-gray-100">
                        {order.items.map(item => (
                            <div key={item.id} className="flex justify-between p-4">
                                <div>
                                    <p className="text-sm font-medium text-gray-900">{item.name}</p>
                                    <p className="text-sm text-gray-500">{item.quantity} × {formatPrice(item.unit_price)}</p>
                                </div>
                                <p className="text-sm font-medium text-gray-900">{formatPrice(item.line_total)}</p>
                            </div>
                        ))}
                    </div>

                    <div className="bg-white rounded-lg shadow-sm p-6 space-y-2 text-sm">
                        <div className="flex justify-between"><span>Subtotal</span><span>{formatPrice(order.subtotal)}</span></div>
                        <div className="flex justify-between"><span>Fulfillment</span><span>{order.fulfillment_fee === 0 ? 'FREE' : formatPrice(order.fulfillment_fee)}</span></div>
                        {order.tip > 0 && <div className="flex justify-between"><span>Tip</span><span>{formatPrice(order.tip)}</span></div>}
                        <div className="flex justify-between"><span>Tax</span><span>{formatPrice(order.tax)}</span></div>
                        <div className="flex justify-between font-semibold pt-2 border-t border-gray-200">
                            <span>Total</span><span>{formatPrice(order.total)}</span>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

Show.layout = {
    breadcrumbs: [
        {
            title: 'Orders #1',
            href: show(1),
        }
    ],
}
