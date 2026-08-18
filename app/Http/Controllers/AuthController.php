<?php
namespace App\Http\Controllers;

use App\Mail\LoginOtpMail;
use App\Models\AuthenticationLog;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLoginForm(){return view('auth.login');}

    public function login(Request $request)
    {
        $data=$request->validate(['email'=>['required','email'],'password'=>['required']]);
        $data['email']=strtolower(trim($data['email']));
        $key='login:'.$data['email'].'|'.$request->ip();
        if(RateLimiter::tooManyAttempts($key,5)){
            $seconds=RateLimiter::availableIn($key);
            return back()->withErrors(['email'=>'Too many sign-in attempts. Please try again in '.ceil($seconds/60).' minute(s).'])->onlyInput('email');
        }
        $user=User::where('email',$data['email'])->first();
        if(!$user||!Hash::check($data['password'],$user->password)){
            RateLimiter::hit($key,60);
            if($user)AuthenticationLog::create(['user_id'=>$user->id,'email'=>$user->email,'event'=>'LOGIN_ATTEMPT','status'=>'FAILED','ip_address'=>$request->ip(),'user_agent'=>$request->userAgent(),'details'=>'Invalid credentials']);
            else AuthenticationLog::create(['email'=>$data['email'],'event'=>'LOGIN_ATTEMPT','status'=>'FAILED','ip_address'=>$request->ip(),'user_agent'=>$request->userAgent(),'details'=>'Invalid credentials']);
            return back()->withErrors(['email'=>'The provided credentials do not match our records.'])->onlyInput('email');
        }
        RateLimiter::clear($key);
        if(!$user->is_active)return back()->withErrors(['email'=>'This account is inactive. Contact an administrator.'])->onlyInput('email');

        // OTP is restricted to privileged inventory managers/admins. Staff and customers use password login.
        if(!$user->isManager()){
            Auth::login($user);
            $request->session()->regenerate();
            AuthenticationLog::create(['user_id'=>$user->id,'email'=>$user->email,'event'=>'LOGIN','status'=>'SUCCESS','ip_address'=>$request->ip(),'user_agent'=>$request->userAgent(),'details'=>'Password login completed without manager OTP requirement']);
            return redirect()->intended(route('products.index'));
        }

        $code=(string)random_int(100000,999999);
        $challenge=OtpChallenge::create(['user_id'=>$user->id,'code_hash'=>Hash::make($code),'expires_at'=>now()->addMinutes((int)env('OTP_EXPIRY_MINUTES',5)),'last_sent_at'=>now(),'ip_address'=>$request->ip()]);
        $request->session()->put('otp_challenge_id',$challenge->id);
        Mail::to($user->email)->send(new LoginOtpMail($code,$user->name,(int)env('OTP_EXPIRY_MINUTES',5)));
        return redirect()->route('otp.form');
    }

    public function showOtpForm(Request $request){
        abort_unless($request->session()->has('otp_challenge_id'),403);
        $challenge=OtpChallenge::with('user')->findOrFail($request->session()->get('otp_challenge_id'));
        abort_unless($challenge->user->isManager(),403);
        return view('auth.otp',compact('challenge'));
    }

    public function verifyOtp(Request $request){
        $request->validate(['otp'=>['required','digits:6']]);
        $challenge=OtpChallenge::with('user')->find($request->session()->get('otp_challenge_id'));
        if(!$challenge||!$challenge->user->isManager()||$challenge->verified_at||$challenge->expires_at->isPast())return back()->withErrors(['otp'=>'This OTP has expired. Please sign in again to receive a new code.']);
        if($challenge->attempts >= (int)env('OTP_MAX_ATTEMPTS',5))return back()->withErrors(['otp'=>'Too many attempts. Please sign in again.']);
        $challenge->increment('attempts');
        if(!Hash::check($request->otp,$challenge->code_hash)){
            AuthenticationLog::create(['user_id'=>$challenge->user_id,'email'=>$challenge->user->email,'event'=>'OTP_VERIFICATION','status'=>'FAILED','ip_address'=>$request->ip(),'user_agent'=>$request->userAgent(),'details'=>'Invalid OTP']);
            return back()->withErrors(['otp'=>'Invalid verification code.']);
        }
        $challenge->update(['verified_at'=>now()]);
        Auth::login($challenge->user);
        $request->session()->forget('otp_challenge_id');
        $request->session()->regenerate();
        AuthenticationLog::create(['user_id'=>$challenge->user_id,'email'=>$challenge->user->email,'event'=>'LOGIN','status'=>'SUCCESS','ip_address'=>$request->ip(),'user_agent'=>$request->userAgent(),'details'=>'Manager password and email OTP verified']);
        return redirect()->intended(route('products.index'));
    }

    public function resendOtp(Request $request){
        $challenge=OtpChallenge::with('user')->find($request->session()->get('otp_challenge_id'));
        abort_unless($challenge&&$challenge->user->isManager(),403);
        if($challenge->last_sent_at&&$challenge->last_sent_at->addSeconds((int)env('OTP_RESEND_SECONDS',60))->isFuture())return back()->withErrors(['otp'=>'Please wait before requesting another code.']);
        $code=(string)random_int(100000,999999);
        $challenge->update(['code_hash'=>Hash::make($code),'expires_at'=>now()->addMinutes((int)env('OTP_EXPIRY_MINUTES',5)),'attempts'=>0,'last_sent_at'=>now(),'verified_at'=>null]);
        Mail::to($challenge->user->email)->send(new LoginOtpMail($code,$challenge->user->name,(int)env('OTP_EXPIRY_MINUTES',5)));
        return back()->with('success','A new verification code has been sent.');
    }

    public function showRegistrationForm(){return view('auth.register');}

    public function register(Request $request)
    {
        $request->validate(['name'=>['required','string','max:255'],'email'=>['required','email','max:255','unique:users,email'],'password'=>['required','string','min:8','confirmed']]);
        User::create(['name'=>$request->name,'email'=>strtolower(trim($request->email)),'password'=>Hash::make($request->password),'role'=>'customer','is_active'=>true]);
        return redirect()->route('login')->with('success','Customer account created. Sign in to continue.');
    }

    public function logout(Request $request){$user=Auth::user();if($user)AuthenticationLog::create(['user_id'=>$user->id,'email'=>$user->email,'event'=>'LOGOUT','status'=>'SUCCESS','ip_address'=>$request->ip(),'user_agent'=>$request->userAgent(),'details'=>'User logged out']);Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('login');}
}
