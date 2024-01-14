<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Rules\Recaptcha;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'login';
    }

    protected function credentials(Request $request)
    {
        $request->request->add([
            'ativo' => true,
            'temp' => false,
        ]);

        return $request->only($this->username(), 'password', 'ativo', 'temp');
    }

    protected function validateLogin(Request $request)
    {
        if (env('APP_ENV') != 'local') {
            $this->validate($request, [
                $this->username() => 'required',
                'password' => 'required',
                'g-recaptcha-response' => ['required', new Recaptcha()]
            ]);
        } else {
            $this->validate($request, [
                $this->username() => 'required',
                'password' => 'required',
            ]);
        }

    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        \DB::table('acessos')->insert([
            'user_id' => \Auth::user()->id,
            'ip' => $request->ip(),
            'created_at' => (new DataHora())->dataHoraInsert(),
            'updated_at' => (new DataHora())->dataHoraInsert(),
        ]);
        \Auth::user()->update(['ultimo_acesso' => (new DataHora())->dataHoraInsert()]);

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    public function teste(Request $request)
    {
        return view('teste');
    }
}
