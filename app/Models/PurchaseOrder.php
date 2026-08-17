<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;
    protected $fillable = ['order_number','supplier_id','created_by','status','order_date','expected_date','received_date','subtotal','tax_amount','total_amount','notes'];
    protected $casts = ['order_date'=>'date','expected_date'=>'date','received_date'=>'date','subtotal'=>'decimal:2','tax_amount'=>'decimal:2','total_amount'=>'decimal:2'];
    public function supplier(){return $this->belongsTo(Supplier::class);}
    public function creator(){return $this->belongsTo(User::class,'created_by');}
    public function items(){return $this->hasMany(PurchaseOrderItem::class);}
}
