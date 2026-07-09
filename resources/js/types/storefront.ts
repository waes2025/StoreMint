export interface DbProduct {
    id: number;
    name: string;
    slug: string;
    price: number;
    compare_at_price: number | null;
    stock_status: string;
    stock: number;
    image: string | null;
    short_description: string | null;
    description: string | null;
    is_featured: boolean;
    is_best_seller: boolean;
    category: string;
}

export interface DbCoupon {
    code: string;
    description: string;
    discountType: 'flat' | 'percentage';
    discountValue: number;
    minOrderAmount: number;
    maxDiscountAmount: number | null;
}

export interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    originalPrice?: number;
    rating: number;
    reviewsCount: number;
    category: string;
    imageGradient: string;
    stock: number;
    badge?: string;
    badgeColor?: string;
    image?: string | null;
}

export interface Coupon {
    code: string;
    description: string;
    discountType: 'flat' | 'percentage';
    discountValue: number;
    minOrderAmount: number;
    maxDiscountAmount?: number;
}

export interface CartItem {
    product: Product;
    quantity: number;
}

export interface OrderInvoice {
    invoiceNo: string;
    orderNo: string;
    date: string;
    paymentMethod: string;
    paymentStatus: 'Paid' | 'Pending' | 'Failed';
    customer: {
        name: string;
        email: string;
        address: string;
        city: string;
        zip: string;
        phone: string;
        paymentMethod: 'cod' | 'sslcommerz' | 'stripe';
    };
    items: {
        name: string;
        price: number;
        quantity: number;
        total: number;
    }[];
    subtotal: number;
    discount: number;
    couponCode?: string;
    shipping: number;
    grandTotal: number;
}
