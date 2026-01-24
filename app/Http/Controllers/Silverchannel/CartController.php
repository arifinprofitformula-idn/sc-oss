<?php

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\StoreOperationalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected StoreOperationalService $storeOperationalService;

    public function __construct(StoreOperationalService $storeOperationalService)
    {
        $this->storeOperationalService = $storeOperationalService;
    }

    public function index()
    {
        $user = User::findOrFail(Auth::id());
        $cartItems = $user->cart()->with('product')->get();
        
        // Ensure price is correct (fallback logic similar to checkout)
        // Convert to array to ensure dynamic properties are preserved in JSON
        $cartItems->transform(function ($item) {
            $item->price_final = $item->product->price_silverchannel ?? $item->product->price_final;
            $item->subtotal = $item->price_final * $item->quantity;
            return $item;
        });

        // We also need to append these to the toArray output if we want them in JSON
        // Or we can just rely on the view using the transformed collection if we pass it carefully
        // But for safety with json_encode in blade:
        $cartData = $cartItems->map(function ($item) {
            $item->product->price_final = $item->price_final; // Attach to product for easier access if needed
            $item->setAttribute('price_final', $item->price_final); // Try setting as attribute
            return $item;
        });

        $total = $cartItems->sum('subtotal');

        return view('silverchannel.cart.index', ['cartItems' => $cartItems, 'total' => $total]);
    }

    public function getItems()
    {
        $user = User::findOrFail(Auth::id());
        $cartItems = $user->cart()->with('product')->get();

        $data = $cartItems->map(function ($item) {
            return [
                'id' => $item->id, // Cart ID
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->product->price_silverchannel ?? $item->product->price_final, // Use correct price field
                'image' => $item->product->image ? asset('storage/' . $item->product->image) : '',
                'stock' => $item->product->stock,
                'quantity' => $item->quantity,
            ];
        });

        $subtotal = $data->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });

        return response()->json([
            'items' => $data,
            'subtotal' => $subtotal,
            'count' => $data->sum('quantity')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $status = $this->storeOperationalService->getStatus();

        if (empty($status['can_add_to_cart'])) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Toko sedang tutup. Tidak dapat menambah ke keranjang.',
                ], 403);
            }

            abort(403, 'Toko sedang tutup. Tidak dapat menambah ke keranjang.');
        }

        $product = Product::findOrFail($request->product_id);

        // Check stock
        if ($product->stock < $request->quantity) {
             if ($request->wantsJson()) {
                 return response()->json(['message' => 'Stok tidak mencukupi.'], 422);
             }
             return back()->with('error', 'Stok tidak mencukupi.');
        }

        $cartItem = Cart::where('user_id', Auth::id())
                        ->where('product_id', $product->id)
                        ->first();

        if ($cartItem) {
            if (($cartItem->quantity + $request->quantity) > $product->stock) {
                 if ($request->wantsJson()) {
                     return response()->json(['message' => 'Total quantity exceeds stock.'], 422);
                 }
                 return back()->with('error', 'Total quantity exceeds stock.');
            }
            $cartItem->increment('quantity', $request->quantity);
        } else {
            $cartItem = Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $request->quantity
            ]);
        }

        if ($request->wantsJson()) {
            // Refresh to get latest data including product
            $cartItem->load('product');
            return response()->json([
                'message' => 'Product added to cart.',
                'item' => [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'name' => $cartItem->product->name,
                    'price' => $cartItem->product->price_silverchannel ?? $cartItem->product->price_final,
                    'image' => $cartItem->product->image ? asset('storage/' . $cartItem->product->image) : '',
                    'stock' => $cartItem->product->stock,
                    'quantity' => $cartItem->quantity,
                ]
            ]);
        }

        return redirect()->route('silverchannel.cart.index')->with('success', 'Product added to cart.');
    }

    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Check Stock
        if ($cart->product->stock < $request->quantity) {
             if ($request->wantsJson()) {
                 return response()->json(['message' => "Stok hanya tersedia {$cart->product->stock}"], 422);
             }
             return back()->with('error', "Stok hanya tersedia {$cart->product->stock}");
        }

        $cart->update(['quantity' => $request->quantity]);

        if ($request->wantsJson()) {
            $price = $cart->product->price_silverchannel ?? $cart->product->price_final;
            $user = User::findOrFail(Auth::id());
            $cartTotal = $user->cart()->with('product')->get()->sum(function($item) {
                $p = $item->product->price_silverchannel ?? $item->product->price_final;
                return $p * $item->quantity;
            });
            return response()->json([
                'success' => true,
                'message' => 'Cart updated.',
                'item_total' => $price * $cart->quantity,
                'cart_total' => $cartTotal
            ]);
        }

        return redirect()->back()->with('success', 'Cart updated.');
    }

    public function destroy(Request $request, Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            abort(403);
        }

        $cart->delete();

        if ($request->wantsJson()) {
            $user = User::findOrFail(Auth::id());
            $cartTotal = $user->cart()->with('product')->get()->sum(function($item) {
                $p = $item->product->price_silverchannel ?? $item->product->price_final;
                return $p * $item->quantity;
            });
            return response()->json([
                'success' => true, 
                'message' => 'Item removed.',
                'cart_total' => $cartTotal
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    public function validateCheckout(Request $request)
    {
        $status = $this->storeOperationalService->getStatus();
        if (empty($status['can_add_to_cart'])) {
            return response()->json(['success' => false, 'message' => 'Toko sedang tutup. Tidak dapat melakukan checkout.'], 403);
        }

        $user = User::findOrFail(Auth::id());
        $cartItems = $user->cart()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Keranjang belanja kosong.'], 400);
        }

        foreach ($cartItems as $item) {
            if ($item->quantity > $item->product->stock) {
                return response()->json([
                    'success' => false, 
                    'message' => "Stok untuk {$item->product->name} tidak mencukupi (Tersedia: {$item->product->stock})."
                ], 422);
            }
        }

        // Redirect URL to checkout
        return response()->json([
            'success' => true,
            'redirect_url' => route('silverchannel.checkout.index')
        ]);
    }
}
