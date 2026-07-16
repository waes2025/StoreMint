import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    DbProduct,
    DbCoupon,
    Product,
    Coupon,
    CartItem,
    OrderInvoice,
} from '@/types/storefront';
import { useAppearance } from '@/composables/useAppearance';

export function useStorefront(props: {
    dbProducts?: DbProduct[];
    dbCategories?: string[];
    dbBrands?: string[];
    dbCoupons?: DbCoupon[];
    gateways?: {
        stripe: {
            enabled: boolean;
            publishable_key: string;
            secret_key: string;
        };
        sslcommerz: {
            enabled: boolean;
            store_id: string;
            store_password: string;
        };
        cod: { enabled: boolean };
    };
}) {
    const { appearance, resolvedAppearance, updateAppearance } =
        useAppearance();
    const isDarkMode = computed(() => resolvedAppearance.value === 'dark');

    const page = usePage();
    const currencySymbol = computed(
        () => (page.props.currency_symbol as string) || '$',
    );

    // State variables
    const searchQuery = ref('');
    const selectedCategory = ref('All');
    const cartOpen = ref(false);
    const selectedProduct = ref<Product | null>(null);
    const viewMode = ref<
        | 'browse'
        | 'categories'
        | 'new-arrivals'
        | 'support'
        | 'checkout'
        | 'confirmation'
    >('browse');

    // Support & Interactive elements state
    const supportForm = ref({
        name: '',
        email: '',
        subject: '',
        message: '',
    });
    const supportSubmitted = ref(false);
    const expandedFaq = ref<number | null>(null);
    const chatMessages = ref<{ sender: 'user' | 'agent'; text: string }[]>([
        {
            sender: 'agent',
            text: '👋 Hi there! I am Minty, your StoreMint support assistant. Select a quick query below to get started!',
        },
    ]);

    const chatOptions = [
        'Where is my order?',
        'Can I get a discount?',
        'Return policy help',
    ];

    const chatProcessing = ref(false);

    // Toast / Feedback message
    const toastMessage = ref('');
    const triggerToast = (msg: string) => {
        toastMessage.value = msg;
        setTimeout(() => {
            if (toastMessage.value === msg) {
                toastMessage.value = '';
            }
        }, 3000);
    };

    const sendChatMessage = (option: string) => {
        if (chatProcessing.value) return;

        chatMessages.value.push({ sender: 'user', text: option });
        chatProcessing.value = true;

        setTimeout(() => {
            let reply = '';
            if (option === 'Where is my order?') {
                reply =
                    '📦 StoreMint orders are shipped within 24 hours of payment. You can track your shipment using the tracking link sent to your registered email, or view active orders inside your Admin Dashboard!';
            } else if (option === 'Can I get a discount?') {
                reply =
                    '💸 Absolutely! You can use code MINT50 at checkout to get an amazing 50% discount on all items in your cart. Check the discount banner on our home page for details!';
            } else if (option === 'Return policy help') {
                reply =
                    '🔄 We support a full 30-day hassle-free refund policy. If you are not satisfied with your purchase, please submit a support ticket here or email support@storemint.com.';
            } else {
                reply =
                    'Thank you for reaching out! Let us know how we can assist you.';
            }

            chatMessages.value.push({ sender: 'agent', text: reply });
            chatProcessing.value = false;
        }, 800);
    };

    const submitSupportTicket = () => {
        if (
            !supportForm.value.name ||
            !supportForm.value.email ||
            !supportForm.value.message
        ) {
            triggerToast('⚠️ Please fill in all required fields.');
            return;
        }
        supportSubmitted.value = true;
        triggerToast('🎟️ Support ticket created!');
        setTimeout(() => {
            supportForm.value = {
                name: '',
                email: '',
                subject: '',
                message: '',
            };
            supportSubmitted.value = false;
        }, 5000);
    };

    // Cart State
    const cart = ref<CartItem[]>([]);

    // Coupon State
    const activeCoupons = computed<Coupon[]>(() => {
        if (props.dbCoupons) {
            return props.dbCoupons.map((c) => ({
                code: c.code,
                description: c.description,
                discountType: c.discountType,
                discountValue: c.discountValue,
                minOrderAmount: c.minOrderAmount,
                maxDiscountAmount: c.maxDiscountAmount || undefined,
            }));
        }
        return [];
    });

    const couponInput = ref('');
    const appliedCoupon = ref<Coupon | null>(null);
    const couponError = ref('');
    const couponSuccess = ref('');

    // Determine default payment method
    let defaultPaymentMethod: 'stripe' | 'sslcommerz' | 'cod' = 'cod';
    if (props.gateways) {
        if (props.gateways.stripe?.enabled) {
            defaultPaymentMethod = 'stripe';
        } else if (props.gateways.sslcommerz?.enabled) {
            defaultPaymentMethod = 'sslcommerz';
        } else if (props.gateways.cod?.enabled) {
            defaultPaymentMethod = 'cod';
        }
    }

    // Checkout form state
    const checkoutForm = ref({
        name: '',
        email: '',
        address: '',
        city: '',
        zip: '',
        phone: '',
        paymentMethod: defaultPaymentMethod,
    });

    // Stripe checkout mock state
    const stripeCard = ref({
        number: '4242 •••• •••• 4242',
        expiry: '12/29',
        cvc: '***',
        isProcessing: false,
    });

    // Invoice detail state
    const orderInvoice = ref<OrderInvoice | null>(null);

    // Active products mapper
    const activeProducts = computed<Product[]>(() => {
        if (props.dbProducts) {
            return props.dbProducts.map((p) => ({
                id: p.id,
                name: p.name,
                description: p.short_description || p.description || '',
                price: p.price,
                originalPrice: p.compare_at_price || undefined,
                rating: 4.5 + (p.id % 5) * 0.1,
                reviewsCount: 15 + (p.id % 10) * 12,
                category: p.category,
                brand: p.brand || 'Generic',
                imageGradient:
                    p.id % 3 === 0
                        ? 'from-slate-700 to-indigo-950'
                        : p.id % 3 === 1
                          ? 'from-teal-600 to-emerald-900'
                          : 'from-amber-600 to-orange-950',
                stock: p.stock,
                badge: p.is_best_seller
                    ? 'Best Seller'
                    : p.is_featured
                      ? 'Featured'
                      : undefined,
                badgeColor: p.is_best_seller
                    ? 'bg-indigo-600 text-white'
                    : 'bg-emerald-500 text-white',
                image: p.image,
            }));
        }
        return [];
    });

    // Computed Category List
    const categories = computed(() => {
        if (props.dbCategories) {
            return props.dbCategories;
        }
        return [];
    });

    // Computed Brand List
    const brands = computed(() => {
        if (props.dbBrands) {
            return props.dbBrands;
        }
        const uniqueBrands = new Set<string>();
        uniqueBrands.add('All');
        activeProducts.value.forEach((p) => {
            if (p.brand) {
                uniqueBrands.add(p.brand);
            }
        });
        return Array.from(uniqueBrands);
    });

    // Filter states
    const selectedBrand = ref('All');
    const minPrice = ref<number | null>(null);
    const maxPrice = ref<number | null>(null);
    const showInStockOnly = ref(false);
    const sortBy = ref('featured');

    // Filtered Products
    const filteredProducts = computed(() => {
        let prods = [...activeProducts.value];

        // Search query filter
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            prods = prods.filter(
                (p) =>
                    p.name.toLowerCase().includes(query) ||
                    p.description.toLowerCase().includes(query),
            );
        }

        // Category filter
        if (selectedCategory.value !== 'All') {
            prods = prods.filter((p) => p.category === selectedCategory.value);
        }

        // Brand filter
        if (selectedBrand.value !== 'All') {
            prods = prods.filter((p) => p.brand === selectedBrand.value);
        }

        // Price range filter
        if (minPrice.value !== null) {
            prods = prods.filter((p) => p.price >= (minPrice.value as number));
        }
        if (maxPrice.value !== null) {
            prods = prods.filter((p) => p.price <= (maxPrice.value as number));
        }

        // Availability filter
        if (showInStockOnly.value) {
            prods = prods.filter((p) => p.stock > 0);
        }

        // Sorting logic
        if (sortBy.value === 'price-asc') {
            prods.sort((a, b) => a.price - b.price);
        } else if (sortBy.value === 'price-desc') {
            prods.sort((a, b) => b.price - a.price);
        } else if (sortBy.value === 'rating') {
            prods.sort((a, b) => b.rating - a.rating);
        } else if (sortBy.value === 'best-seller') {
            prods.sort(
                (a, b) =>
                    (b.badge === 'Best Seller' ? 1 : 0) -
                    (a.badge === 'Best Seller' ? 1 : 0),
            );
        }

        return prods;
    });

    // Featured Products
    const featuredProducts = computed(() => {
        if (props.dbProducts && props.dbProducts.length > 0) {
            const featured = activeProducts.value.filter(
                (p) => p.badge === 'Featured' || p.badge === 'Trending',
            );
            if (featured.length > 0) return featured.slice(0, 4);
        }
        return activeProducts.value.filter((p) => p.id % 2 === 0).slice(0, 4);
    });

    // Best Seller Products
    const bestSellerProducts = computed(() => {
        if (props.dbProducts && props.dbProducts.length > 0) {
            const best = activeProducts.value.filter(
                (p) => p.badge === 'Best Seller' || p.badge === 'Hot Deal',
            );
            if (best.length > 0) return best.slice(0, 4);
        }
        return activeProducts.value.filter((p) => p.id % 2 !== 0).slice(0, 4);
    });

    // Cart Calculations
    const cartSubtotal = computed(() => {
        return cart.value.reduce(
            (total, item) => total + item.product.price * item.quantity,
            0,
        );
    });

    const discountAmount = computed(() => {
        if (!appliedCoupon.value) return 0;
        const coupon = appliedCoupon.value;
        if (cartSubtotal.value < coupon.minOrderAmount) {
            return 0;
        }
        if (coupon.discountType === 'flat') {
            return coupon.discountValue;
        } else {
            const calculated =
                (cartSubtotal.value * coupon.discountValue) / 100;
            if (
                coupon.maxDiscountAmount &&
                calculated > coupon.maxDiscountAmount
            ) {
                return coupon.maxDiscountAmount;
            }
            return calculated;
        }
    });

    const isShipmentEnabled = computed(() => {
        const enabledModules = (page.props.enabled_modules as string[]) || [];
        return enabledModules.includes('Shipment');
    });

    const isCartEnabled = computed(() => {
        const enabledModules = (page.props.enabled_modules as string[]) || [];
        return enabledModules.includes('Cart');
    });

    const shippingFee = computed(() => {
        if (!isShipmentEnabled.value) return 0;
        if (cartSubtotal.value === 0) return 0;
        return cartSubtotal.value > 200 ? 0 : 15.0;
    });

    const cartTotal = computed(() => {
        return Math.max(
            0,
            cartSubtotal.value - discountAmount.value + shippingFee.value,
        );
    });

    const cartQuantity = computed(() => {
        return cart.value.reduce((total, item) => total + item.quantity, 0);
    });

    // Cart Actions
    const addToCart = (product: Product, quantity = 1) => {
        if (product.stock === 0) {
            triggerToast('⚠️ Product is out of stock!');
            return;
        }

        const existing = cart.value.find(
            (item) => item.product.id === product.id,
        );
        if (existing) {
            if (existing.quantity + quantity > product.stock) {
                triggerToast(
                    `⚠️ Cannot add more. Only ${product.stock} items in stock.`,
                );
                return;
            }
            existing.quantity += quantity;
        } else {
            cart.value.push({ product, quantity });
        }
        triggerToast(`🛒 Added "${product.name}" to cart!`);

        if (appliedCoupon.value) {
            revalidateCoupon();
        }
    };

    const updateCartQuantity = (productId: number, delta: number) => {
        const item = cart.value.find((i) => i.product.id === productId);
        if (!item) return;

        const newQty = item.quantity + delta;
        if (newQty <= 0) {
            removeFromCart(productId);
        } else if (newQty > item.product.stock) {
            triggerToast(`⚠️ Only ${item.product.stock} items in stock.`);
        } else {
            item.quantity = newQty;
        }

        if (appliedCoupon.value) {
            revalidateCoupon();
        }
    };

    const removeFromCart = (productId: number) => {
        cart.value = cart.value.filter((item) => item.product.id !== productId);
        triggerToast('🗑️ Item removed from cart.');
        if (appliedCoupon.value) {
            revalidateCoupon();
        }
    };

    // Coupon Actions
    const applyCoupon = () => {
        couponError.value = '';
        couponSuccess.value = '';
        const code = couponInput.value.trim().toUpperCase();

        if (!code) {
            couponError.value = 'Please enter a coupon code.';
            return;
        }

        const coupon = activeCoupons.value.find((c) => c.code === code);
        if (!coupon) {
            couponError.value = 'Invalid coupon code. Try MINT50 or WELCOME10.';
            return;
        }

        if (cartSubtotal.value < coupon.minOrderAmount) {
            couponError.value = `Minimum order amount of ${currencySymbol.value}${coupon.minOrderAmount.toFixed(2)} required for this coupon.`;
            return;
        }

        appliedCoupon.value = coupon;
        couponSuccess.value = `Coupon "${coupon.code}" applied successfully!`;
        triggerToast(`🏷️ Coupon "${coupon.code}" applied!`);
    };

    const removeCoupon = () => {
        appliedCoupon.value = null;
        couponInput.value = '';
        couponSuccess.value = '';
        couponError.value = '';
        triggerToast('🏷️ Coupon removed.');
    };

    const revalidateCoupon = () => {
        if (!appliedCoupon.value) return;
        if (cartSubtotal.value < appliedCoupon.value.minOrderAmount) {
            const code = appliedCoupon.value.code;
            appliedCoupon.value = null;
            couponError.value = `Coupon "${code}" was removed because order amount fell below ${currencySymbol.value}${activeCoupons.value.find((c) => c.code === code)?.minOrderAmount}.`;
        }
    };

    // Checkout Steps
    const proceedToCheckout = () => {
        if (cart.value.length === 0) {
            triggerToast('⚠️ Your cart is empty!');
            return;
        }
        viewMode.value = 'checkout';
        cartOpen.value = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const placeOrder = () => {
        const executeCheckout = () => {
            const csrfToken =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '';
            fetch('/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    customer: checkoutForm.value,
                    items: cart.value.map((item) => ({
                        product_id: item.product.id,
                        quantity: item.quantity,
                        price: item.product.price,
                        product: {
                            name: item.product.name,
                            price: item.product.price,
                        },
                    })),
                    subtotal: cartSubtotal.value,
                    discount: discountAmount.value,
                    couponCode: appliedCoupon.value?.code,
                    shipping: shippingFee.value,
                    grandTotal: cartTotal.value,
                }),
            })
                .then((res) => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then((data) => {
                    if (data.success) {
                        orderInvoice.value = data.invoice;
                        cart.value = [];
                        appliedCoupon.value = null;
                        couponInput.value = '';
                        viewMode.value = 'confirmation';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        triggerToast('🎉 Order placed successfully!');
                    } else {
                        triggerToast(
                            '⚠️ Failed to place order. Please try again.',
                        );
                    }
                })
                .catch((err) => {
                    console.error(err);
                    triggerToast('⚠️ Error connecting to server.');
                });
        };

        if (checkoutForm.value.paymentMethod === 'stripe') {
            stripeCard.value.isProcessing = true;
            setTimeout(() => {
                stripeCard.value.isProcessing = false;
                executeCheckout();
            }, 1500);
        } else {
            executeCheckout();
        }
    };

    const handlePrint = () => {
        triggerToast('🖨️ PDF Invoice download triggered!');
    };

    const resetStorefront = () => {
        viewMode.value = 'browse';
        orderInvoice.value = null;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const scrollToCollection = () => {
        window.scrollTo({ top: 500, behavior: 'smooth' });
    };

    const selectCategory = (cat: string) => {
        selectedCategory.value = cat;
        viewMode.value = 'browse';
        window.scrollTo({ top: 500, behavior: 'smooth' });
    };

    return {
        // Dark Mode
        isDarkMode,
        appearance,
        resolvedAppearance,
        updateAppearance,

        // Core UI Navigation State
        searchQuery,
        selectedCategory,
        cartOpen,
        selectedProduct,
        viewMode,

        // Support desk state & functions
        supportForm,
        supportSubmitted,
        expandedFaq,
        chatMessages,
        chatOptions,
        chatProcessing,
        sendChatMessage,
        submitSupportTicket,

        // Cart State & Calculations
        cart,
        cartSubtotal,
        discountAmount,
        shippingFee,
        cartTotal,
        cartQuantity,
        addToCart,
        updateCartQuantity,
        removeFromCart,

        // Coupons
        activeCoupons,
        couponInput,
        appliedCoupon,
        couponError,
        couponSuccess,
        applyCoupon,
        removeCoupon,

        // Checkout
        checkoutForm,
        stripeCard,
        orderInvoice,
        proceedToCheckout,
        placeOrder,

        // Toast
        toastMessage,
        triggerToast,

        isShipmentEnabled,
        isCartEnabled,

        // Products & Categories & Brands
        categories,
        brands,
        selectedBrand,
        activeProducts,
        minPrice,
        maxPrice,
        showInStockOnly,
        sortBy,
        filteredProducts,
        featuredProducts,
        bestSellerProducts,

        // Global utilities
        handlePrint,
        resetStorefront,
        scrollToCollection,
        selectCategory,
    };
}
