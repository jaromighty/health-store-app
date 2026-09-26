import { router } from '@inertiajs/react';
import { useState } from 'react';
import {Store} from "@/types";
import {add} from "@/routes/cart";

interface StoreSelectorProps {
    currentStore: Store | null;
    stores: Store[];
}

export default function StoreSelector({ currentStore, stores }: StoreSelectorProps) {
    const [isOpen, setIsOpen] = useState(false);

    const selectStore = (store: Store) => {
        // In a real app, you might need to clear the cart or warn the user
        // For MVP, we just switch the store context
        router.post(add({}), {
            // Actually, switching store usually requires clearing cart or migrating items
            // Let's just set a session or local state for now, or redirect to cart with new store
            // Simplified: Just redirect to cart with the new store ID in query
            // router.get(index(), { store_id: store.id });
        });
        setIsOpen(false);
    };

    return (
        <div className="relative">
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-purple-600"
            >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clipRule="evenodd" />
                </svg>
                <span>{currentStore ? `${currentStore.name} (${currentStore.city})` : 'Select Store'}</span>
            </button>

            {isOpen && (
                <div className="absolute right-0 mt-2 w-72 bg-white rounded-md shadow-lg z-50 border border-gray-200">
                    <div className="p-2">
                        <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Pickup Location</h3>
                        {stores.map(store => (
                            <button
                                key={store.id}
                                onClick={() => selectStore(store)}
                                className={`w-full text-left px-3 py-2 rounded-md text-sm ${
                                    currentStore?.id === store.id ? 'bg-purple-100 text-purple-700' : 'hover:bg-gray-50'
                                }`}
                            >
                                <div className="font-medium">{store.name}</div>
                                <div className="text-gray-500 text-xs">{store.address}, {store.city}</div>
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
