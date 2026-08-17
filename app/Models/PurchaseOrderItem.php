<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id','product_id','quantity_ordered','quantity_received','unit_cost','line_total'];
    protected $casts = ['unit_cost'=>'decimal:2','line_total'=>'decimal:2'];
    public function order(){return $this->belongsTo(PurchaseOrder::class,'purchase_order_id');}
    public function product(){return $this->belongsTo(Products::class,'product_id');}
}
