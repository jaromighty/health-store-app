import OrderStatusBadge from '@/components/order-status-badge';
import { Head, Link } from '@inertiajs/react';
import { Order, PaginatedData } from '@/types';
import {index, show} from "@/routes/orders";

export default function Index({ orders }: { orders: PaginatedData<Order> }) {
    const formatPrice = (n: number) =>
        new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n);

    return (
        <>
            <Head title="Order History" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    {orders.data.length === 0 ? (
                        <div className="text-center py-12 bg-white rounded-lg shadow-sm">
                            <p className="text-gray-500">No orders yet. Your first healthy haul awaits!</p>
                        </div>
                    ) : (
                        <div className="bg-white shadow-sm rounded-lg divide-y divide-gray-100">
                            {orders.data.map(order => (
                                <Link
                                    key={order.id}
                                    href={show(order)}
                                    className="flex items-center justify-between p-5 hover:bg-gray-50"
                                >
                                    <div>
                                        <div className="flex items-center gap-3">
                                            <span className="font-medium text-gray-900">{order.number}</span>
                                            <OrderStatusBadge status={order.status} />
                                        </div>
                                        <p className="text-sm text-gray-500 mt-1">
                                            {order.items.length} item{order.items.length !== 1 ? 's' : ''} · {order.store.name}
                                        </p>
                                    </div>
                                    <div className="text-right">
                                        <p className="font-semibold text-gray-900">{formatPrice(order.total)}</p>
                                        <p className="text-sm text-gray-500">{new Date(order.created_at).toLocaleDateString()}</p>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

Index.layout = {
    breadcrumbs: [
        {
            title: 'Your Orders',
            href: index(),
        }
    ],
}
