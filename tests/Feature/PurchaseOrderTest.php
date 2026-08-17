<?php

namespace Tests\Feature;

use App\Models\Products;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::create(['name'=>'Manager','email'=>'po-manager@example.test','password'=>Hash::make('password'),'role'=>'manager','is_active'=>true]);
    }

    public function test_manager_can_create_purchase_order(): void
    {
        $manager=$this->manager(); $supplier=Supplier::create(['name'=>'Acme Supplies','status'=>'active']); $product=Products::create(['product_name'=>'Keyboard','sku'=>'KB-001','product_price'=>50000,'cost_price'=>30000,'category'=>'Electronics','stock_quantity'=>10,'reorder_level'=>3,'is_active'=>true]);
        $response=$this->actingAs($manager)->post(route('purchase-orders.store'),['supplier_id'=>$supplier->id,'order_date'=>now()->toDateString(),'items'=>[['product_id'=>$product->id,'quantity_ordered'=>5,'unit_cost'=>28000]]]);
        $response->assertRedirect(); $this->assertDatabaseHas('purchase_orders',['supplier_id'=>$supplier->id,'status'=>'draft']); $this->assertDatabaseHas('purchase_order_items',['product_id'=>$product->id,'quantity_ordered'=>5]);
    }

    public function test_receiving_purchase_order_increases_stock_and_records_movement(): void
    {
        $manager=$this->manager(); $supplier=Supplier::create(['name'=>'Acme Supplies','status'=>'active']); $product=Products::create(['product_name'=>'Mouse','sku'=>'MS-001','product_price'=>25000,'cost_price'=>12000,'category'=>'Electronics','stock_quantity'=>10,'reorder_level'=>3,'is_active'=>true]);
        $this->actingAs($manager)->post(route('purchase-orders.store'),['supplier_id'=>$supplier->id,'order_date'=>now()->toDateString(),'items'=>[['product_id'=>$product->id,'quantity_ordered'=>5,'unit_cost'=>10000]]]);
        $order=\App\Models\PurchaseOrder::first(); $this->actingAs($manager)->post(route('purchase-orders.send',$order)); $item=$order->items()->first();
        $response=$this->actingAs($manager)->post(route('purchase-orders.receive',$order),['items'=>[$item->id=>['quantity_received'=>5]]]);
        $response->assertRedirect(); $this->assertDatabaseHas('products',['id'=>$product->id,'stock_quantity'=>15]); $this->assertDatabaseHas('stock_movements',['product_id'=>$product->id,'type'=>'STOCK_IN','quantity'=>5]); $this->assertDatabaseHas('purchase_orders',['id'=>$order->id,'status'=>'received']);
    }
}
