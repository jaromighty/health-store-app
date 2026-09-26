import {Link, router} from '@inertiajs/react';
import {Product} from "@/types";
import {show} from "@/routes/products";
import {add} from "@/routes/cart";

interface ProductCardProps {
    product: Product;
}

export default function ProductCard({ product }: ProductCardProps) {
    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(price);
    };

    // Note: To make this robust, you would typically store the selected_store_id in the user's session or a global
    // React context. For this MVP, you can pass the store ID from the parent layout or URL.
    const addToCart = () => {
        // In a real app, check if store is selected first
        // For now, we assume a default store or redirect
        router.post(add(), {
            product_id: product.id,
            store_id: 1, // Replace with dynamic store ID from context/state
            quantity: 1
        });
    };

    return (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200 flex flex-col h-full">
            {/* Image Placeholder */}
            <div className="h-48 bg-gray-100 flex items-center justify-center relative">
                {product.image_url ? (
                    <img src={product.image_url} alt={product.name} className="w-full h-full object-cover" />
                ) : (
                    <span className="text-gray-400 text-sm">No Image</span>
                )}
            </div>

            <div className="p-4 flex flex-col flex-grow">
                <Link href={show(product.id)} className="block group">
                    <h3 className="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors line-clamp-2">
                        {product.name}
                    </h3>
                </Link>

                <p className="text-sm text-gray-500 mt-1 line-clamp-2">{product.short_description}</p>

                <div className="mt-2 flex flex-wrap gap-1">
                    {product.certifications && product.certifications.map((cert) => (
                        <span
                            key={cert}
                            className="inline-flex items px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 capitalize"
                        >
              {cert.replace('_', ' ')}
            </span>
                    ))}
                </div>

                <div className="mt-auto pt-4 flex items-center justify-between">
                    <div>
                        <span className="text-lg font-bold text-gray-900">{formatPrice(product.price)}</span>
                        {product.unit_type && (
                            <span className="text-sm text-gray-500 ml-1">/{product.unit_type}</span>
                        )}
                    </div>

                    <button
                        className="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-3 py-1.5 rounded transition-colors"
                        onClick={(e) => {
                            e.preventDefault();
                            addToCart()
                        }}
                    >
                        Add
                    </button>
                </div>
            </div>
        </div>
    );
}
