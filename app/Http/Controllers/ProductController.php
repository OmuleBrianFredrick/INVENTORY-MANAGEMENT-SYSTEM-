<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Products;
use App\Models\StockMovement;
use App\Services\InventoryAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class ProductController extends Controller
{
    private function manager(Request $r): void
    {
        abort_unless($r->user()->isManager(), 403);
    }

    private function resolveCategory(array &$data): void
    {
        if (!empty($data['category_id'])) {
            $category = Category::whereKey($data['category_id'])->where('is_active', true)->firstOrFail();
            $data['category'] = $category->name;
            return;
        }

        $name = trim((string) ($data['category'] ?? ''));
        $category = Category::firstOrCreate(
            ['name' => $name],
            ['description' => null, 'is_active' => true]
        );
        abort_unless($category->is_active, 422, 'The selected category is archived.');
        $data['category_id'] = $category->id;
        $data['category'] = $category->name;
    }

    public function index(Request $r)
    {
        $products = Products::with('categoryRelation')
            ->when($r->q, fn($q, $term) => $q->where(fn($x) => $x
                ->where('product_name', 'like', '%'.$term.'%')
                ->orWhere('sku', 'like', '%'.$term.'%')
                ->orWhere('barcode', 'like', '%'.$term.'%')))
            ->orderBy('product_name')->get();
        $totalProducts = $products->count();
        $totalStock = $products->sum('stock_quantity');
        $lowStock = $products->filter(fn($p) => $p->is_low_stock)->count();
        $recentMovements = StockMovement::with(['product', 'user'])->latest()->limit(8)->get();
        return view('products.productList', compact('products', 'totalProducts', 'totalStock', 'lowStock', 'recentMovements'));
    }

    public function create(Request $r)
    {
        $this->manager($r);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $r)
    {
        $this->manager($r);
        $data = $r->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'description' => 'nullable|string',
            'product_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|string|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'product_image' => 'nullable|image|max:2048',
        ]);
        if (empty($data['category_id']) && empty($data['category'])) {
            return back()->withErrors(['category_id' => 'A product category is required.'])->withInput();
        }
        $this->resolveCategory($data);
        if ($r->hasFile('product_image')) {
            $data['product_image'] = basename($r->file('product_image')->store('images', 'public'));
        }
        $product = DB::transaction(function () use ($data, $r) {
            $product = Products::create($data);
            if ($product->stock_quantity > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $r->user()->id,
                    'type' => 'OPENING',
                    'quantity' => $product->stock_quantity,
                    'balance_before' => 0,
                    'balance_after' => $product->stock_quantity,
                    'reason' => 'Opening stock',
                ]);
            }
            return $product;
        });
        app(InventoryAlertService::class)->lowStock($product);
        return Redirect::route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Request $r, $id)
    {
        $this->manager($r);
        $product = Products::findOrFail($id);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $r, $id)
    {
        $this->manager($r);
        $product = Products::findOrFail($id);
        $data = $r->validate([
            'product_name' => 'required|string|max:255',
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.$product->id],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode,'.$product->id],
            'description' => 'nullable|string',
            'product_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|string|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'product_image' => 'nullable|image|max:2048',
        ]);
        if (empty($data['category_id']) && empty($data['category'])) {
            return back()->withErrors(['category_id' => 'A product category is required.'])->withInput();
        }
        $this->resolveCategory($data);
        if ($r->hasFile('product_image')) {
            $data['product_image'] = basename($r->file('product_image')->store('images', 'public'));
        }
        $old = $product->stock_quantity;
        DB::transaction(function () use ($product, $data, $old, $r) {
            $product->update($data);
            $new = $product->stock_quantity;
            if ($new !== $old) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $r->user()->id,
                    'type' => 'ADJUSTMENT',
                    'quantity' => abs($new - $old),
                    'balance_before' => $old,
                    'balance_after' => $new,
                    'reason' => 'Manual product edit',
                ]);
            }
        });
        app(InventoryAlertService::class)->lowStock($product->fresh());
        return Redirect::route('products.index')->with('success', 'Product updated successfully.');
    }

    public function delete(Request $r, $id)
    {
        $this->manager($r);
        Products::findOrFail($id)->delete();
        return Redirect::route('products.index')->with('success', 'Product archived successfully.');
    }

    public function stockIn(Request $r, $id)
    {
        $this->manager($r);
        $data = $r->validate(['quantity' => 'required|integer|min:1', 'reason' => 'nullable|string|max:255']);
        $product = Products::findOrFail($id);
        DB::transaction(function () use ($product, $data, $r) {
            $before = $product->stock_quantity;
            $product->increment('stock_quantity', $data['quantity']);
            StockMovement::create(['product_id' => $product->id, 'user_id' => $r->user()->id, 'type' => 'STOCK_IN', 'quantity' => $data['quantity'], 'balance_before' => $before, 'balance_after' => $product->fresh()->stock_quantity, 'reason' => $data['reason'] ?? 'Stock received']);
        });
        app(InventoryAlertService::class)->lowStock($product->fresh());
        return back()->with('success', 'Stock received successfully.');
    }

    public function stockOut(Request $r, $id)
    {
        $this->manager($r);
        $data = $r->validate(['quantity' => 'required|integer|min:1', 'reason' => 'nullable|string|max:255']);
        $product = Products::findOrFail($id);
        if ($data['quantity'] > $product->stock_quantity) {
            return back()->withErrors(['quantity' => 'Stock out quantity cannot exceed current stock.']);
        }
        DB::transaction(function () use ($product, $data, $r) {
            $before = $product->stock_quantity;
            $product->decrement('stock_quantity', $data['quantity']);
            StockMovement::create(['product_id' => $product->id, 'user_id' => $r->user()->id, 'type' => 'STOCK_OUT', 'quantity' => $data['quantity'], 'balance_before' => $before, 'balance_after' => $product->fresh()->stock_quantity, 'reason' => $data['reason'] ?? 'Stock issued']);
        });
        app(InventoryAlertService::class)->lowStock($product->fresh());
        return back()->with('success', 'Stock issued successfully.');
    }
}
