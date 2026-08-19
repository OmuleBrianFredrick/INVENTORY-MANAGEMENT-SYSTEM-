<?php

namespace Tests\Feature;

use App\Models\Products;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MarketplaceCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_browse_public_marketplace_without_account(): void
    {
        Products::create(['product_name' => 'Demo Phone', 'sku' => 'DEMO-001', 'description' => 'A demo product.', 'product_price' => 500000, 'cost_price' => 400000, 'category' => 'Electronics', 'stock_quantity' => 5, 'reorder_level' => 1, 'is_active' => true]);
        $this->get(route('marketplace.home'))->assertOk()->assertSee('Demo Phone')->assertSee('UJUZI SHOP MALL');
    }

    public function test_guest_can_add_product_to_session_cart_without_account(): void
    {
        $product = Products::create(['product_name' => 'Demo Shoes', 'sku' => 'DEMO-002', 'product_price' => 120000, 'cost_price' => 80000, 'category' => 'Fashion', 'stock_quantity' => 4, 'reorder_level' => 1, 'is_active' => true]);
        $this->post(route('cart.add', $product), ['quantity' => 2])->assertRedirect();
        $this->get(route('cart.index'))->assertOk()->assertSee('Demo Shoes')->assertSee('240,000');
        $this->assertSame(2, session('shop_cart')[$product->id]);
    }

    public function test_customer_registration_does_not_destroy_guest_cart(): void
    {
        $product = Products::create(['product_name' => 'Demo Blender', 'sku' => 'DEMO-003', 'product_price' => 180000, 'cost_price' => 120000, 'category' => 'Home', 'stock_quantity' => 3, 'reorder_level' => 1, 'is_active' => true]);
        $this->withSession(['shop_cart' => [(string) $product->id => 1]])->post(route('register.post'), ['name' => 'Mall Customer', 'email' => 'mall-customer@example.test', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['email' => 'mall-customer@example.test', 'role' => 'customer']);
        $this->assertSame(1, session('shop_cart')[$product->id]);
    }

    public function test_customer_login_returns_to_storefront(): void
    {
        $customer = User::create(['name' => 'Shopper', 'email' => 'shopper@example.test', 'password' => Hash::make('password123'), 'role' => 'customer', 'is_active' => true]);
        $this->post(route('login.post'), ['email' => $customer->email, 'password' => 'password123'])->assertRedirect(route('marketplace.home'));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_customer_can_reach_checkout_with_saved_cart(): void
    {
        $customer = User::create(['name' => 'Checkout Shopper', 'email' => 'checkout@example.test', 'password' => Hash::make('password123'), 'role' => 'customer', 'is_active' => true]);
        $product = Products::create(['product_name' => 'Checkout Item', 'sku' => 'DEMO-004', 'product_price' => 75000, 'cost_price' => 50000, 'category' => 'Home', 'stock_quantity' => 3, 'reorder_level' => 1, 'is_active' => true]);
        $this->actingAs($customer)->withSession(['shop_cart' => [(string) $product->id => 1]])->get(route('checkout'))->assertOk()->assertSee('Complete your order');
    }

    public function test_customer_checkout_creates_order_deducts_stock_and_clears_cart(): void
    {
        $customer = User::create(['name' => 'Buyer', 'email' => 'buyer@example.test', 'password' => Hash::make('password123'), 'role' => 'customer', 'is_active' => true]);
        $product = Products::create(['product_name' => 'Checkout Phone', 'sku' => 'DEMO-005', 'product_price' => 300000, 'cost_price' => 220000, 'category' => 'Electronics', 'stock_quantity' => 5, 'reorder_level' => 1, 'is_active' => true]);
        $response = $this->actingAs($customer)->withSession(['shop_cart' => [(string) $product->id => 2]])->post(route('checkout.store'), ['delivery_address' => 'Kampala, Uganda', 'notes' => 'Call on arrival']);
        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['created_by' => $customer->id, 'status' => 'confirmed', 'payment_status' => 'unpaid']);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'STOCK_OUT', 'quantity' => 2]);
        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertEmpty(session('shop_cart', []));
    }
}
