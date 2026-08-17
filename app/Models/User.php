<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;use Illuminate\Notifications\Notifiable;use Illuminate\Database\Eloquent\Factories\HasFactory;
class User extends Authenticatable {use HasFactory,Notifiable;protected $fillable=['name','email','password','role','is_active'];protected $hidden=['password','remember_token'];protected function casts():array{return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean'];}public function isAdmin():bool{return $this->role==='admin';}public function isManager():bool{return in_array($this->role,['admin','manager'],true);}}
