<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\SoftDeletes;
class Products extends Model {use SoftDeletes;protected $fillable=['product_name','sku','description','product_price','cost_price','category','stock_quantity','reorder_level','product_image','is_active'];protected $casts=['product_price'=>'decimal:2','cost_price'=>'decimal:2','is_active'=>'boolean'];public function stockMovements(){return $this->hasMany(StockMovement::class,'product_id');}public function getIsLowStockAttribute():bool{return $this->stock_quantity <= $this->reorder_level;}}
