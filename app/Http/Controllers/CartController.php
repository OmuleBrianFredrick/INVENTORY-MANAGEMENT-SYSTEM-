<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function cart(Request $request): array
    {
        return $request->session()->get('shop_cart', []);
    }

    private function save(Request $request, array $cart): void
    {
        $request->session()->put('shop_cart', $cart);
    }

    public function index(Request $request)
    {
        $cart = $this->cart($request);
        $products = Products::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $items = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $products->get((int) $productId);
            if (!$product || !$product->is_active) {
                unset($cart[$productId]);
                continue;
            }
            $quantity = min(max((int) $quantity, 1), max((int) $product->stock_quantity, 1));
            $lineTotal = $quantity * (float) $product->product_price;
            $items[] = compact('product', 'quantity', 'lineTotal');
            $total += $lineTotal;
            $cart[$productId] = $quantity;
        }

        $this->save($request, $cart);
        return view('cart.index', compact('items', 'total'));
    }

    public function add(Request $request, Products $product)
    {
        abort_unless($product->is_active, 404);
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1']]);
        $quantity = (int) ($data['quantity'] ?? 1);
        abort_if($product->stock_quantity < $quantity, 422, 'The requested quantity is not available.');

        $cart = $this->cart($request);
        $key = (string) $product->id;
        $cart[$key] = ($cart[$key] ?? 0) + $quantity;
        abort_if($cart[$key] > $product->stock_quantity, 422, 'The requested quantity exceeds available stock.');
        $this->save($request, $cart);

        return back()->with('success', $product->product_name.' was added to your cart.');
    }

    public function update(Request $request)
    {
        $data = $request->validate(['items' => ['required', 'array'], 'items.*' => ['integer', 'min:1']]);
        $cart = $this->cart($request);
        $products = Products::whereIn('id', array_keys($cart))->get()->keyBy('id');
        foreach ($data['items'] as $productId => $quantity) {
            if ($products->has((int) $productId)) {
                $cart[(string) $productId] = min((int) $quantity, $products->get((int) $productId)->stock_quantity);
            }
        }
        $this->save($request, array_filter($cart, fn ($quantity) => $quantity > 0));
        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request, Products $product)
    {
        $cart = $this->cart($request);
        unset($cart[(string) $product->id]);
        $this->save($request, $cart);
        return back()->with('success', 'Product removed from your cart.');
    }
}
