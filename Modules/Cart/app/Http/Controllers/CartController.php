<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;

class CartController extends Controller
{
    /**
     * Display a listing of the resource (retrieve current cart).
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);

        return response()->json([
            'success' => true,
            'cart' => $cart->load('items.product'),
        ]);
    }

    /**
     * Store a newly created resource in storage (add or update item in cart).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getOrCreateCart($request);

        $cartItem = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Item updated in cart successfully.',
            'item' => $cartItem->load('product'),
        ]);
    }

    /**
     * Remove the specified resource from storage (remove item from cart).
     */
    public function destroy(Request $request, $productId): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);

        CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
        ]);
    }

    /**
     * Get or create the cart for the user/session.
     */
    private function getOrCreateCart(Request $request): Cart
    {
        $userId = $request->user()?->id;
        $sessionId = session()->getId();

        if ($userId) {
            return Cart::firstOrCreate(['user_id' => $userId]);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Delete a cart (abandoned/old cart deletion from Admin dashboard).
     */
    public function destroyCart(Request $request, $currentTeam, Cart $cart)
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $cart->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => '🛒 Cart deleted successfully.',
        ]);
    }
}

