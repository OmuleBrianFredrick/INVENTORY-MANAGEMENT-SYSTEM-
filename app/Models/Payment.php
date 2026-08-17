<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model {protected $fillable=['order_id','reference','provider','method','amount','status','paid_at','notes'];protected $casts=['amount'=>'decimal:2','paid_at'=>'datetime'];public function order(){return $this->belongsTo(Order::class);}}
