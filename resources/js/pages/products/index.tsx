import ProductCard from '@/components/product-card';
import FilterSidebar from '@/components/filter-sidebar';
import { Head, Link } from '@inertiajs/react';
import {Category, PaginatedData, Product, ProductFilters} from "@/types";
import {index} from "@/routes/products";

interface ProductIndexProps {
    products: PaginatedData<Product>;
    filters: ProductFilters;
    categories: Category[];
}

export default function Index({ products, filters, categories }: ProductIndexProps) {
    return (
        <>
            <Head title="Shop Products" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-col lg:flex-row gap-8">

                    <FilterSidebar
                        categories={categories}
                        initialFilters={filters}
                    />

                    <div className="flex-1">
                        <div className="mb-4 text-sm text-gray-600">
                            Showing {products.data.length} of {products.total} results
                        </div>

                        {products.data.length === 0 ? (
                            <div className="text-center py-12 bg-gray-50 rounded-lg">
                                <p className="text-gray-500 text-lg">No products found matching your criteria.</p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                {products.data.map(product => (
                                    <ProductCard key={product.id} product={product}/>
                                ))}
                            </div>
                        )}

                        {products.links.length > 3 && (
                            <div className="mt-8 flex justify-center space-x-2">
                                {products.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url ?? '#'}
                                        preserveScroll
                                        className={`px-3 py-1 rounded border text-sm ${
                                            link.active
                                                ? 'bg-purple-600 text-white border-purple-600'
                                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                        } ${!link.url ? 'opacity-50 cursor-default' : ''}`}
                                        dangerouslySetInnerHTML={{__html: link.label}}
                                    />
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

Index.layout = {
    breadcrumbs: [
        {
            title: 'Products',
            href: index(),
        }
    ],
};
