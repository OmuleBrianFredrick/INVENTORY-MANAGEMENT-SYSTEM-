<?php
namespace App\Http\Controllers;
use App\Models\Order;use App\Services\PaymentService;use Illuminate\Http\Request;
class PaymentController extends Controller {public function store(Request $r,Order $order,PaymentService $payments){abort_unless($r->user()?->isManager(),403);$data=$r->validate(['amount'=>'required|numeric|min:0.01','method'=>'required|in:cash,bank,mobile_money,card','provider'=>'nullable|string|max:100','reference'=>'nullable|string|max:100|unique:payments,reference']);$payments->record($order,(float)$data['amount'],$data['method'],$data['provider']??'manual',$data['reference']??null);return back()->with('success','Payment recorded successfully.');}}
