import { router } from '@inertiajs/react';
import {fulfillment} from "@/routes/checkout";

interface FulfillmentToggleProps {
    type: 'delivery' | 'pickup';
    zip?: string;
}

export default function FulfillmentToggle({ type, zip }: FulfillmentToggleProps) {
    const options = [
        { value: 'pickup' as const, label: 'Store Pickup', sub: 'Free · Ready in as little as 2 hours' },
        { value: 'delivery' as const, label: 'Home Delivery', sub: 'From $7.99 · Free over $60' },
    ];

    return (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {options.map(opt => (
                <button
                    key={opt.value}
                    onClick={() => router.get(fulfillment(), { type: opt.value, ...(zip ? { zip } : {}) })}
                    className={`text-left p-4 rounded-lg border-2 transition-colors ${
                        type === opt.value
                            ? 'border-purple-600 bg-purple-50'
                            : 'border-gray-200 hover:border-gray-300'
                    }`}
                >
                    <div className="font-semibold text-gray-900">{opt.label}</div>
                    <div className="text-sm text-gray-500 mt-1">{opt.sub}</div>
                </button>
            ))}
        </div>
    );
}
