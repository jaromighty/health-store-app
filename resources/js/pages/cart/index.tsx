import CartItemRow from '@/components/cart-item-row';
import StoreSelector from '@/components/store-selector';
import { Head, Link } from '@inertiajs/react';
import {index as productIndex} from "@/routes/products";
import {index as cartIndex} from "@/routes/cart";

interface CartItem {
    id: number;
    product: any;
    store: any;
    quantity: number;
    price: number;
    total: number;
    available_stock: number | null;
}

interface CartPageProps {
    cartItems: CartItem[];
    subtotal: number;
    selectedStore: any | null;
    stores: any[]; // Pass stores list if needed, or fetch in component
    flash: any;
}

export default function Cart({ cartItems, subtotal, selectedStore, stores, flash }: CartPageProps) {
    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price);
    };

    return (
        <>
            <Head title="Shopping Cart" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">

                    {/* Flash Messages */}
                    {/*{flash.success && (*/}
                    {/*    <div className="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">*/}
                    {/*        {flash.success}*/}
                    {/*    </div>*/}
                    {/*)}*/}
                    {/*{flash.error && (*/}
                    {/*    <div className="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">*/}
                    {/*        {flash.error}*/}
                    {/*    </div>*/}
                    {/*)}*/}

                    {cartItems.length === 0 ? (
                        <div className="text-center py-12 bg-white rounded-lg shadow-sm">
                            <h3 className="mt-2 text-sm font-medium text-gray-900">Your cart is empty</h3>
                            <p className="mt-1 text-sm text-gray-500">Start adding some healthy groceries!</p>
                            <div className="mt-6">
                                <Link href={productIndex()} className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                    Continue Shopping
                                </Link>
                            </div>
                        </div>
                    ) : (
                        <div className="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div className="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                                <h3 className="text-lg font-medium text-gray-900">Order Summary</h3>
                                <StoreSelector currentStore={selectedStore} stores={stores || []} />
                            </div>

                            <div className="px-4 py-5 sm:p-6">
                                {cartItems.map(item => (
                                    <CartItemRow key={item.id} item={item} />
                                ))}
                            </div>

                            <div className="bg-gray-50 px-4 py-5 sm:px-6 border-t border-gray-200">
                                <div className="flex justify-between text-base font-medium text-gray-900">
                                    <p>Subtotal</p>
                                    <p>{formatPrice(subtotal)}</p>
                                </div>
                                <p className="mt-0.5 text-sm text-gray-500">Shipping and taxes calculated at checkout.</p>
                                <div className="mt-6">
                                    <Link href="#" className="flex items-center justify-center rounded-md border border-transparent bg-purple-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-purple-700">
                                        Proceed to Checkout
                                    </Link>
                                </div>
                                <div className="mt-6 flex justify-center text-center text-sm text-gray-500">
                                    <p>
                                        or{' '}
                                        <Link href={cartIndex()} className="font-medium text-purple-600 hover:text-purple-500">
                                            Continue Shopping
                                            <span aria-hidden="true"> &rarr;</span>
                                        </Link>
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

Cart.layout = {
    breadcrumbs: [
        {
            title: 'Cart',
            href: cartIndex()
        }
    ],
}
