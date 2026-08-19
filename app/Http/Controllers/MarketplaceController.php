<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Products;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function home(Request $request)
    {
        $products = Products::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->when($request->q, fn ($query, $term) => $query->where(function ($q) use ($term) {
                $q->where('product_name', 'like', '%'.$term.'%')
                    ->orWhere('sku', 'like', '%'.$term.'%')
                    ->orWhere('barcode', 'like', '%'.$term.'%');
            }))
            ->when($request->category, fn ($query, $category) => $query->where('category_id', $category))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $featured = Products::where('is_active', true)->where('stock_quantity', '>', 0)->latest('id')->limit(8)->get();

        return view('marketplace.home', compact('products', 'categories', 'featured'));
    }

    public function show(Products $product)
    {
        abort_unless($product->is_active, 404);
        return view('marketplace.show', compact('product'));
    }
}
