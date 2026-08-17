<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\SoftDeletes;
class Supplier extends Model { use SoftDeletes; protected $fillable=['name','contact_person','email','phone','address','tax_number','status','notes']; public function scopeActive($query){return $query->where('status','active');} public function products(){return $this->hasMany(Products::class);}}
