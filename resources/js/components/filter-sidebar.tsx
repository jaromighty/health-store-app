import { router } from '@inertiajs/react';
import {ChangeEvent, useState} from 'react';
import { Category, ProductFilters } from '@/types';
import {index} from "@/routes/products";

interface FilterSidebarProps {
    categories: Category[];
    initialFilters: ProductFilters;
}

export default function FilterSidebar({ categories, initialFilters }: FilterSidebarProps) {
    const [selectedCerts, setSelectedCerts] = useState<string[]>(initialFilters.certifications || []);
    const [selectedCategory, setSelectedCategory] = useState<string>(initialFilters.category_slug || '');

    const certificationOptions = ['organic', 'non_gmo', 'gluten_free', 'vegan', 'kosher'];

    const handleCategoryChange = (e: ChangeEvent<HTMLSelectElement>) => {
        const value = e.target.value;
        setSelectedCategory(value);

        const params: Record<string, any> = {
            ...initialFilters,
            page: 1,
        };

        if (value) params.category_slug = value;
        else delete params.category_slug;

        router.get(index(), params);
    };

    const handleCertToggle = (cert: string) => {
        let newCerts: string[];
        if (selectedCerts.includes(cert)) {
            newCerts = selectedCerts.filter(c => c !== cert);
        } else {
            newCerts = [...selectedCerts, cert];
        }

        setSelectedCerts(newCerts);

        const params: Record<string, any> = {
            ...initialFilters,
            page: 1,
            category_slug: selectedCategory || undefined,
        };

        if (newCerts.length > 0) {
            params.certifications = newCerts;
        } else {
            delete params.certifications;
        }

        router.get(index(), params);
    };

    const resetFilters = () => {
        setSelectedCerts([]);
        setSelectedCategory('');
        router.get(index());
    };

    return (
        <aside className="w-full lg:w-64 space-y-6">
            <div className="flex items-center justify-between">
                <h2 className="text-lg font-semibold text-gray-900">Filters</h2>
                {(selectedCerts.length > 0 || selectedCategory) && (
                    <button
                        onClick={resetFilters}
                        className="text-sm text-purple-600 hover:text-purple-800 underline"
                    >
                        Clear All
                    </button>
                )}
            </div>

            <div>
                <h3 className="text-sm font-medium text-gray-700 mb-2">Category</h3>
                <select
                    value={selectedCategory}
                    onChange={handleCategoryChange}
                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm p-2 border"
                >
                    <option value="">All Categories</option>
                    {categories.map(cat => (
                        <option key={cat.id} value={cat.slug}>{cat.name}</option>
                    ))}
                </select>
            </div>

            <div>
                <h3 className="text-sm font-medium text-gray-700 mb-2">Certifications</h3>
                <div className="space-y-2">
                    {certificationOptions.map(cert => (
                        <label key={cert} className="flex items-center space-x-2 cursor-pointer">
                            <input
                                type="checkbox"
                                checked={selectedCerts.includes(cert)}
                                onChange={() => handleCertToggle(cert)}
                                className="rounded text-purple-600 focus:ring-purple-500 border-gray-300"
                            />
                            <span className="text-sm text-gray-600 capitalize">{cert.replace('_', ' ')}</span>
                        </label>
                    ))}
                </div>
            </div>

            <div>
                <h3 className="text-sm font-medium text-gray-700 mb-2">Search</h3>
                <form onSubmit={(e) => {
                    e.preventDefault();
                    const formData = new FormData(e.currentTarget);
                    const search = formData.get('search') as string;

                    const params: Record<string, any> = {
                        ...initialFilters,
                        page: 1,
                        category_slug: selectedCategory || undefined,
                        search: search || undefined,
                    };

                    if (selectedCerts.length > 0) {
                        params.certifications = selectedCerts;
                    } else {
                        delete params.certifications;
                    }

                    router.get(index(), params);
                }}>
                    <input
                        type="text"
                        name="search"
                        defaultValue={initialFilters.search || ''}
                        placeholder="Search products..."
                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm p-2 border"
                    />
                </form>
            </div>
        </aside>
    );
}
