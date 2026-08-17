<?php
namespace App\Services;
use App\Models\InventoryAlert;use App\Models\Products;use App\Models\User;
class InventoryAlertService {public function lowStock(Products $product):void{if($product->stock_quantity>$product->reorder_level)return;$recipients=User::whereIn('role',['admin','manager'])->where('is_active',true)->get();foreach($recipients as $user){InventoryAlert::firstOrCreate(['product_id'=>$product->id,'user_id'=>$user->id,'type'=>'low_stock','read_at'=>null],['title'=>'Low stock: '.$product->product_name,'message'=>'Stock is '.$product->stock_quantity.', at or below reorder level '.$product->reorder_level.'.']);}}}
