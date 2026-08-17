<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ReturnItem extends Model {protected $fillable=['return_id','order_item_id','product_id','quantity','unit_price','line_total'];protected $casts=['unit_price'=>'decimal:2','line_total'=>'decimal:2'];public function returnRecord(){return $this->belongsTo(ReturnModel::class,'return_id');}public function product(){return $this->belongsTo(Products::class,'product_id');}}
