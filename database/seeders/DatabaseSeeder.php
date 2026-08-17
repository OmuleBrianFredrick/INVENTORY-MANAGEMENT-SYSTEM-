<?php
namespace Database\Seeders;
use App\Models\User;use Illuminate\Database\Seeder;use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder {public function run():void{if(!User::exists()&&env('ADMIN_EMAIL')){User::create(['name'=>env('ADMIN_NAME','System Administrator'),'email'=>env('ADMIN_EMAIL'),'password'=>Hash::make(env('ADMIN_PASSWORD','ChangeMe123!')),'role'=>'admin','is_active'=>true]);}}}
