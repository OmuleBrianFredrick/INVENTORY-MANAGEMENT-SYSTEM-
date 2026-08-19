<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Products;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerCheckoutController extends Controller
{
    private function customer(Request $request): void
    {
        abort_unless($request->user()?->isCustomer(), 403);
    }

    public function create(Request $request)
    {
        $this->customer($request);
        $cart = $request->session()->get('shop_cart', []);
        abort_if(empty($cart), 302, 'Your cart is empty.');
        $products = Products::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $items = [];
        $total = 0;
        foreach ($cart as $id => $quantity) {
            $product = $products->get((int) $id);
            if (!$product || !$product->is_active || $product->stock_quantity < $quantity) {
                return redirect()->route('cart.index')->withErrors(['cart' => 'One or more cart items are no longer available in the requested quantity.']);
            }
            $lineTotal = $quantity * (float) $product->product_price;
            $items[] = compact('product', 'quantity', 'lineTotal');
            $total += $lineTotal;
        }
        return view('checkout.index', compact('items', 'total'));
    }

    public function store(Request $request)
    {
        $this->customer($request);
        $data = $request->validate([
            'delivery_address' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $cart = $request->session()->get('shop_cart', []);
        abort_if(empty($cart), 422, 'Your cart is empty.');

        $order = DB::transaction(function () use ($request, $data, $cart) {
            $customer = Customer::firstOrCreate(
                ['email' => $request->user()->email],
                ['name' => $request->user()->name, 'status' => 'active']
            );
            $customer->update(['name' => $request->user()->name, 'status' => 'active']);

            $order = Order::create([
                'order_number' => 'WEB-'.now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
                'customer_id' => $customer->id,
                'created_by' => $request->user()->id,
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'delivery_status' => 'pending',
                'order_date' => now()->toDateString(),
                'delivery_address' => $data['delivery_address'],
                'notes' => $data['notes'] ?? null,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]);

            $subtotal = 0;
            foreach ($cart as $productId => $quantity) {
                $product = Products::lockForUpdate()->findOrFail($productId);
                abort_if(!$product->is_active || $quantity > $product->stock_quantity, 422, 'Insufficient stock for '.$product->product_name.'.');
                $lineTotal = $quantity * (float) $product->product_price;
                $subtotal += $lineTotal;
                $before = $product->stock_quantity;
                $product->decrement('stock_quantity', $quantity);
                $after = $product->fresh()->stock_quantity;
                OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => $product->product_price, 'line_total' => $lineTotal]);
                StockMovement::create(['product_id' => $product->id, 'user_id' => $request->user()->id, 'type' => 'STOCK_OUT', 'quantity' => $quantity, 'balance_before' => $before, 'balance_after' => $after, 'reason' => 'Web order '.$order->order_number]);
            }
            $order->update(['subtotal' => $subtotal, 'total_amount' => $subtotal]);
            return $order;
        });

        $request->session()->forget('shop_cart');
        return redirect()->route('checkout.confirmation', $order)->with('success', 'Order placed successfully.');
    }

    public function confirmation(Request $request, Order $order)
    {
        $this->customer($request);
        abort_unless($order->created_by === $request->user()->id, 404);
        $order->load(['items.product', 'customer']);
        return view('checkout.confirmation', compact('order'));
    }
}
