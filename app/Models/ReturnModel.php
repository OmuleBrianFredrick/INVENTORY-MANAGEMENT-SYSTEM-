<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ReturnModel extends Model {protected $table='returns';protected $fillable=['return_number','order_id','customer_id','processed_by','status','refund_status','refund_amount','reason'];protected $casts=['refund_amount'=>'decimal:2'];public function order(){return $this->belongsTo(Order::class);}public function customer(){return $this->belongsTo(Customer::class);}public function processor(){return $this->belongsTo(User::class,'processed_by');}public function items(){return $this->hasMany(ReturnItem::class,'return_id');}}
