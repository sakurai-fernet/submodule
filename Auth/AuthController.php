<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use Carbon\Carbon;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Contracts\Config\Repository as Config;
use Auth;
use App\Http\Requests\RegisterUserRequest;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';
    protected $redirectPath = '/users';
    protected $loginPath = '/login';
    protected $redirectAfterLogout = '/login';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => ['logout','register', 'showRegistrationForm']]);
        $this->middleware('confirm', ['only' => 'login']);
        $this->middleware('role', ['only' => 'showRegistrationForm', 'register']);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {

        if(isset($data['auto_password'])){
        $data['password'] = substr(base_convert(md5(uniqid()), 16, 36), 0, 8);
        }

        if(isset($data['force_change_pass'])){
        $data['change_flag'] = 1;
        }else{
        $data['change_flag'] = 0;
        }

        if(isset($data['send_email'])){
            $data['confirmation_token'] = hash_hmac(
                    'sha256',
                    str_random(40).$data['email'],
                    12
                    );
            $data['confirmed_at'] = NULL;
        $this->sendConfMail($data);
        }else{
            $data['confirmation_token'] = NULL;
            $data['confirmed_at'] = Carbon::now();
        }

        if(isset($data['add_role'])){
            $data['role'] = 'admin';
        }else{
            $data['role'] = 'user';
        }

       $data['confirmation_sent_at'] = Carbon::now();

        return User::create([
            'name' => $data['name'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'confirmation_token' => $data['confirmation_token'],
            'confirmed_at' => $data['confirmed_at'],
            'confirmation_sent_at' => $data['confirmation_sent_at'],
            'change_flag' => $data['change_flag'],
            'role' => $data['role'],
        ]);
     }


    public function sendConfMail(array $data)
    {
          Mail::send('auth.emails.confmail', ['user' => $data,'token' => $data['confirmation_token']], function($m) use ($data){
                 $m->to($data['email'], $data['name'])->subject('アカウント登録の確認');
          });
     }

     public function postRegister(Request $request)
     {
         return $this->register($request);
     }

     public function register(RegisterUserRequest $request)
     {
         $this->create($request->all());
         \Session::flash('flash_message', 'ユーザーを登録しました。');
         return redirect('users');
     }


     public function login(Request $request)
     {
         $throttles = $this->isUsingThrottlesLoginsTrait();
         if ($throttles && $lockedOut = $this->hasTooManyLoginAttempts($request)) {
             $this->fireLockoutEvent($request);

             return $this->sendLockoutResponse($request);
         }

         $emailORname = $request->input('text');
         $password = $request->input('password');
         if (Auth::guard($this->getGuard())->attempt(['email' => $emailORname, 'password' => $password],$request->has('remember'))) {
             // 認証通過…
             return $this->handleUserWasAuthenticated($request, $throttles);
         }else if(Auth::guard($this->getGuard())->attempt(['name' => $emailORname, 'password' => $password],$request->has('remember'))){
             return $this->handleUserWasAuthenticated($request, $throttles);
         }

         if ($throttles && ! $lockedOut) {
             $this->incrementLoginAttempts($request);
         }

         return $this->sendFailedLoginResponse($request);
     }

     public function getConfirm($token) {
         $user = User::where('confirmation_token', '=', $token)->first();
         if (! $user) {
             \Session::flash('flash_message', '無効なトークンです。');
             return redirect('login');
         }

         $user->confirm();
         $user->save();

         \Session::flash('flash_message', 'ユーザー登録が完了しました。ログインしてください。');
         return redirect('login');
     }

     public function authenticated(Request $request, User $user)
     {
         if($user['change_flag']==1){
             return redirect('passwordsetting');
         }
        if($user->role == 'admin'){
         return redirect('users');
        }else{
           return redirect ('mypage');
        }
     }
}
