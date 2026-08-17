<?php
namespace App\Http\Controllers;
use App\Models\InventoryAlert;use Illuminate\Http\Request;
class AlertController extends Controller {public function index(Request $r){$alerts=InventoryAlert::with('product')->where('user_id',$r->user()->id)->latest()->paginate(20);return view('alerts.index',compact('alerts'));}public function read(Request $r,InventoryAlert $alert){abort_unless($alert->user_id===$r->user()->id,403);$alert->update(['read_at'=>now()]);return back();}public function readAll(Request $r){InventoryAlert::where('user_id',$r->user()->id)->whereNull('read_at')->update(['read_at'=>now()]);return back()->with('success','All alerts marked as read.');}}
