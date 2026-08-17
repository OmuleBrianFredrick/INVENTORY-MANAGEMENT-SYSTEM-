<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OtpChallenge extends Model {protected $fillable=['user_id','code_hash','expires_at','verified_at','attempts','last_sent_at','ip_address'];protected $casts=['expires_at'=>'datetime','verified_at'=>'datetime','last_sent_at'=>'datetime'];public function user(){return $this->belongsTo(User::class);}}
