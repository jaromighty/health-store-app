import { router } from '@inertiajs/react';
import {remove, update} from "@/routes/cart";
import {Product} from "@/types";

interface CartItem {
    id: number;
    product: Product;
    quantity: number;
    total: number;
    available_stock: number | null;
}

interface CartItemRowProps {
    item: CartItem;
}

export default function CartItemRow({ item }: CartItemRowProps) {
    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price);
    };

    const updateQuantity = (newQty: number) => {
        if (newQty === 0) {
            router.delete(remove(item.id));
        } else {
            router.patch(update(item.id), { quantity: newQty });
        }
    };

    const isOutOfStock = item.available_stock !== null && item.quantity > item.available_stock;

    return (
        <div className="flex items-center py-4 border-b border-gray-200">
            <div className="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                {item.product.image_url ? (
                    <img src={item.product.image_url} alt={item.product.name} className="h-full w-full object-cover" />
                ) : (
                    <div className="h-full w-full bg-gray-100 flex items-center justify-center text-gray-400 text-xs">No Img</div>
                )}
            </div>

            <div className="ml-4 flex flex-1 flex-col">
                <div>
                    <div className="flex justify-between text-base font-medium text-gray-900">
                        <h3>{item.product.name}</h3>
                        <p className="ml-4">{formatPrice(item.total)}</p>
                    </div>
                    <p className="mt-1 text-sm text-gray-500">{item.product.unit_type ? `Per ${item.product.unit_type}` : ''}</p>
                </div>

                {isOutOfStock && (
                    <div className="mt-1 text-xs text-red-600 font-medium">
                        Only {item.available_stock} left in stock!
                    </div>
                )}

                <div className="flex flex-1 items-end justify-between text-sm">
                    <div className="flex items-center border border-gray-300 rounded-md">
                        <button
                            onClick={() => updateQuantity(item.quantity - 1)}
                            className="px-2 py-1 text-gray-600 hover:bg-gray-100"
                            disabled={item.quantity <= 1}
                        >-</button>
                        <span className="px-2 font-medium">{item.quantity}</span>
                        <button
                            onClick={() => updateQuantity(item.quantity + 1)}
                            className="px-2 py-1 text-gray-600 hover:bg-gray-100"
                            disabled={item.available_stock !== null && item.quantity >= item.available_stock}
                        >+</button>
                    </div>
                    <button
                        type="button"
                        onClick={() => updateQuantity(0)}
                        className="font-medium text-purple-600 hover:text-purple-500"
                    >
                        Remove
                    </button>
                </div>
            </div>
        </div>
    );
}
