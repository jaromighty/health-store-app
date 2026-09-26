import type { ReactNode } from 'react';
import type { BreadcrumbItem } from '@/types/navigation';

export type AppLayoutProps = {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
};

export type AppVariant = 'header' | 'sidebar';

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};

export type AuthLayoutProps = {
    children?: ReactNode;
    name?: string;
    title?: string;
    description?: string;
};

export type Category = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export type Product = {
    id: number;
    category_id: number;
    name: string;
    slug: string;
    sku: string;
    description: string | null;
    price: number;
    unit_price: number | null;
    unit_type: string | null;
    image_url: string | null;
    certifications: string[];
    short_description: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    category?: Category;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

export interface ProductFilters {
    search?: string;
    category_slug?: string;
    certifications?: string[];
    page?: number;
}

export interface Store {
    id: number;
    name: string;
    city: string;
    state: string;
    address: string;
}

export interface TimeSlotOption {
    id: number;
    date: string;
    starts_at: string;
    ends_at: string;
    label: string;
    remaining: number;
    almost_full: boolean;
}

export interface SlotDayGroup {
    date: string;
    label: string;
    slots: TimeSlotOption[];
}

export interface FeeQuote {
    fee: number;
    tip: number;
    free_delivery_threshold: number;
}

export interface FulfillmentPageProps {
    store: { id: number; name: string; city: string; state: string; address: string };
    type: 'delivery' | 'pickup';
    zip: string | null;
    zipError: string | null;
    slotsByDate: SlotDayGroup[];
    subtotal: number;
    fees: FeeQuote;
}

export type OrderStatus =
    | 'pending_payment' | 'confirmed' | 'shopping'
    | 'ready_for_pickup' | 'out_for_delivery' | 'completed' | 'cancelled';

export interface OrderItem {
    id: number;
    product_id: number;
    name: string;
    sku: string;
    quantity: number;
    unit_price: number;
    line_total: number;
}

export interface Order {
    id: number;
    number: string;
    user_id: number;
    store: { id: number; name: string; city: string; state: string };
    fulfillment?: {
        type: 'delivery' | 'pickup';
        status: string;
        scheduled_window?: string | null;
        delivery_address?: string | null;
    } | null;
    items: OrderItem[];
    subtotal: number;
    fulfillment_fee: number;
    tip: number;
    tax: number;
    total: number;
    status: OrderStatus;
    paid_at: string | null;
    created_at: string;
}

export interface ReviewPageProps {
    order: Order;
    paymentInit: { provider: string; client_secret: string };
}
