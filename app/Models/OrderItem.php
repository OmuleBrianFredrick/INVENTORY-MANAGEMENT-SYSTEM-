<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model {protected $fillable=['order_id','product_id','quantity','unit_price','line_total'];protected $casts=['unit_price'=>'decimal:2','line_total'=>'decimal:2'];public function order(){return $this->belongsTo(Order::class);}public function product(){return $this->belongsTo(Products::class,'product_id');}}
