<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuthenticationLog extends Model {protected $fillable=['user_id','email','event','status','ip_address','user_agent','details'];public function user(){return $this->belongsTo(User::class);}}
