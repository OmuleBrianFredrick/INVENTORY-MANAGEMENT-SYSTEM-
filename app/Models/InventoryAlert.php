<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InventoryAlert extends Model {protected $fillable=['product_id','user_id','type','title','message','read_at'];protected $casts=['read_at'=>'datetime'];public function product(){return $this->belongsTo(Products::class,'product_id');}public function user(){return $this->belongsTo(User::class);}}
