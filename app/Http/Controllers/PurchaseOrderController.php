<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    private function manager(Request $request): void { abort_unless($request->user()?->isManager(), 403); }

    public function index(Request $request)
    {
        $this->manager($request);
        $orders = PurchaseOrder::with('supplier')->when($request->q, fn($q,$term)=>$q->where('order_number','like','%'.$term.'%')->orWhereHas('supplier',fn($s)=>$s->where('name','like','%'.$term.'%')))->orderByDesc('id')->paginate(15)->withQueryString();
        return view('purchase-orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $this->manager($request);
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Products::where('is_active',true)->orderBy('product_name')->get();
        return view('purchase-orders.create', compact('suppliers','products'));
    }

    public function store(Request $request)
    {
        $this->manager($request);
        $data = $request->validate([
            'supplier_id'=>'required|exists:suppliers,id',
            'order_date'=>'required|date',
            'expected_date'=>'nullable|date|after_or_equal:order_date',
            'notes'=>'nullable|string|max:2000',
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|distinct|exists:products,id',
            'items.*.quantity_ordered'=>'required|integer|min:1',
            'items.*.unit_cost'=>'required|numeric|min:0',
        ]);
        $supplier = Supplier::active()->findOrFail($data['supplier_id']);
        $order = DB::transaction(function() use ($data,$request) {
            $order = PurchaseOrder::create([
                'order_number'=>'PO-'.now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(3)),0,6)),
                'supplier_id'=>$data['supplier_id'],'created_by'=>$request->user()->id,'status'=>'draft',
                'order_date'=>$data['order_date'],'expected_date'=>$data['expected_date'] ?? null,'notes'=>$data['notes'] ?? null,
            ]);
            $subtotal=0;
            foreach($data['items'] as $item){$line=$item['quantity_ordered']*(float)$item['unit_cost'];$subtotal+=$line;PurchaseOrderItem::create(['purchase_order_id'=>$order->id,'product_id'=>$item['product_id'],'quantity_ordered'=>$item['quantity_ordered'],'unit_cost'=>$item['unit_cost'],'line_total'=>$line]);}
            $order->update(['subtotal'=>$subtotal,'total_amount'=>$subtotal]);
            return $order;
        });
        return redirect()->route('purchase-orders.show',$order)->with('success','Purchase order created successfully.');
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->manager($request);
        $purchaseOrder->load(['supplier','creator','items.product']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function send(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->manager($request);
        abort_if($purchaseOrder->status !== 'draft', 422, 'Only draft purchase orders can be sent.');
        $purchaseOrder->update(['status'=>'ordered']);
        return back()->with('success','Purchase order marked as ordered.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->manager($request);
        $data=$request->validate(['items'=>'required|array','items.*.quantity_received'=>'required|integer|min:0']);
        DB::transaction(function() use ($purchaseOrder,$data,$request) {
            $purchaseOrder->load('items.product');
            foreach($purchaseOrder->items as $item){
                $additional=(int)($data['items'][$item->id]['quantity_received'] ?? 0);
                $remaining=$item->quantity_ordered-$item->quantity_received;
                if($additional>$remaining) abort(422,'Received quantity cannot exceed the remaining ordered quantity.');
                if($additional===0) continue;
                $product=$item->product; $before=$product->stock_quantity; $product->increment('stock_quantity',$additional); $after=$product->fresh()->stock_quantity;
                $item->increment('quantity_received',$additional);
                StockMovement::create(['product_id'=>$product->id,'user_id'=>$request->user()->id,'type'=>'STOCK_IN','quantity'=>$additional,'balance_before'=>$before,'balance_after'=>$after,'reason'=>'Purchase order '.$purchaseOrder->order_number.' received']);
            }
            $purchaseOrder->refresh()->load('items');
            $allReceived=$purchaseOrder->items->every(fn($item)=>$item->quantity_received >= $item->quantity_ordered);
            $anyReceived=$purchaseOrder->items->contains(fn($item)=>$item->quantity_received > 0);
            $purchaseOrder->update(['status'=>$allReceived?'received':($anyReceived?'partial':'ordered'),'received_date'=>$allReceived?now()->toDateString():$purchaseOrder->received_date]);
        });
        return back()->with('success','Goods receipt processed and stock updated.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->manager($request);
        abort_if(in_array($purchaseOrder->status,['received','cancelled']),422,'This purchase order cannot be cancelled.');
        $purchaseOrder->update(['status'=>'cancelled']);
        return back()->with('success','Purchase order cancelled.');
    }
}
