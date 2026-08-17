<?php
namespace App\Http\Controllers;
use App\Models\AuthenticationLog;use Illuminate\Http\Request;
class SecurityLogController extends Controller {public function index(Request $r){abort_unless($r->user()->isAdmin(),403);$logs=AuthenticationLog::with('user')->latest()->limit(200)->get();return view('security.logs',compact('logs'));}}
