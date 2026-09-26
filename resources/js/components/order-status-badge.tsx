import {OrderStatus} from '@/types';

const CONFIG: Record<OrderStatus, { label: string; classes: string }> = {
    pending_payment: {label: 'Awaiting Payment', classes: 'bg-yellow-100 text-yellow-800'},
    confirmed: {label: 'Confirmed', classes: 'bg-blue-100 text-blue-800'},
    shopping: {label: 'Being Shopped', classes: 'bg-indigo-100 text-indigo-800'},
    ready_for_pickup: {label: 'Ready for Pickup', classes: 'bg-green-100 text-green-800'},
    out_for_delivery: {label: 'Out for Delivery', classes: 'bg-teal-100 text-teal-800'},
    completed: {label: 'Delivered', classes: 'bg-gray-100 text-gray-700'},
    cancelled: {label: 'Cancelled', classes: 'bg-red-100 text-red-800'},
};

export default function OrderStatusBadge({status}: { status: OrderStatus }) {
    const conf = CONFIG[status] ?? CONFIG.completed;
    return (
        <span className={`inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold ${conf.classes}`}>
          {conf.label}
        </span>
    );
}
