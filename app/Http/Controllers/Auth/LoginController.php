<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Rules\Recaptcha;
use App\Services\Auth\MfaLoginService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use MasterTag\DataHora;
use RuntimeException;

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
        ]);

        return $request->only($this->username(), 'password', 'ativo');
    }

    protected function validateLogin(Request $request)
    {
        $regras = [
            $this->username() => 'required',
            'password' => 'required',
        ];

        if (!in_array(env('APP_ENV'), ['local', 'testing'], true)) {
            $regras['g-recaptcha-response'] = ['required', new Recaptcha()];
        }

        $this->validate($request, $regras);
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $user = $this->retrieveActiveUser($request);
        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse($request);
        }

        $mfa = app(MfaLoginService::class);
        if ($mfa->empresaExigeMfa($user)) {
            try {
                $mfa->iniciarChallenge($user, $request, $request->boolean('remember'));
            } catch (RuntimeException $e) {
                return redirect()->back()
                    ->withInput($request->only($this->username()))
                    ->withErrors([$this->username() => $e->getMessage()]);
            }

            $this->clearLoginAttempts($request);

            return redirect()->route('login.mfa.show')
                ->with('success', 'Enviamos um código de verificação. Informe-o para continuar.');
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function showMfaForm(Request $request, MfaLoginService $mfa)
    {
        $user = $mfa->userPendente($request);
        if (!$user) {
            return redirect()->route('login')
                ->withErrors([$this->username() => 'Sessão de verificação expirada. Faça login novamente.']);
        }

        $destinos = $mfa->destinosSessao($request);
        $cooldown = $mfa->podeReenviar($user);
        $tentativas = $mfa->statusTentativas($user);

        return view('auth.mfa', [
            'destinosMascarados' => $destinos['destinos_mascarados'] ?? [],
            'canais' => $destinos['canais'] ?? [],
            'ttlMinutos' => $mfa->ttlMinutos(),
            'codigoSegundos' => $mfa->segundosRestantesCodigo($user),
            'reenvioSegundos' => (int) ($cooldown['segundos_restantes'] ?? 0),
            'bloqueioSegundos' => !empty($tentativas['bloqueado'])
                ? (int) ($tentativas['segundos_restantes'] ?? 0)
                : 0,
        ]);
    }

    public function verificarMfa(Request $request, MfaLoginService $mfa)
    {
        $user = $mfa->userPendente($request);
        if (!$user) {
            return redirect()->route('login')
                ->withErrors([$this->username() => 'Sessão de verificação expirada. Faça login novamente.']);
        }

        $statusTentativas = $mfa->statusTentativas($user);
        if ($statusTentativas['bloqueado']) {
            return redirect()->route('login.mfa.show')->withErrors([
                'codigo' => 'Muitas tentativas inválidas. Aguarde o tempo indicado para tentar novamente.',
            ]);
        }

        $request->validate([
            'codigo' => 'required|string|max:8',
        ]);

        if (!$mfa->validarCodigo($user, (string) $request->input('codigo'))) {
            $status = $mfa->registrarFalha($user);
            $msg = 'Código inválido ou expirado.';
            if ($status['bloqueado']) {
                $msg = 'Muitas tentativas inválidas. Aguarde o tempo indicado para tentar novamente.';
            } elseif ($status['tentativas_restantes'] > 0) {
                $msg .= ' Tentativas restantes: ' . $status['tentativas_restantes'] . '.';
            }

            return redirect()->route('login.mfa.show')->withErrors(['codigo' => $msg]);
        }

        $remember = $mfa->rememberPendente($request);
        $mfa->invalidarCodigo($user);
        $mfa->limparTentativas($user);
        $mfa->limparChallengeSessao($request);

        $this->guard()->login($user, $remember);

        if ($request->hasSession()) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        return $this->sendLoginResponse($request);
    }

    public function reenviarMfa(Request $request, MfaLoginService $mfa)
    {
        $user = $mfa->userPendente($request);
        if (!$user) {
            return redirect()->route('login')
                ->withErrors([$this->username() => 'Sessão de verificação expirada. Faça login novamente.']);
        }

        try {
            $resultado = $mfa->reenviarCodigo($user);
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors(['codigo' => $e->getMessage()]);
        }

        if (empty($resultado['enviado'])) {
            return redirect()->route('login.mfa.show')->withErrors([
                'codigo' => 'Aguarde o tempo indicado para reenviar o código.',
            ]);
        }

        if (!empty($resultado['destinos_mascarados'])) {
            $pending = $request->session()->get(MfaLoginService::SESSION_PENDING_KEY, []);
            if (is_array($pending)) {
                $pending['canais'] = $resultado['canais'] ?? ($pending['canais'] ?? []);
                $pending['destinos_mascarados'] = $resultado['destinos_mascarados'];
                $request->session()->put(MfaLoginService::SESSION_PENDING_KEY, $pending);
            }
        }

        return redirect()->route('login.mfa.show')->with('success', 'Novo código enviado.');
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
        
        $user = \Auth::user();
        $user->update(['ultimo_acesso' => (new DataHora())->dataHoraInsert()]);
        
        // Verifica se é primeiro acesso ou tem senha temporária
        $isFirstAccess = is_null($user->password_changed_at);
        $hasTemporaryPassword = $user->temp == true;
        
        // Se é primeiro acesso ou tem senha temporária, obriga mudança de senha
        if ($isFirstAccess || $hasTemporaryPassword) {
            // Define que precisa alterar senha
            $user->update([
                'require_password_reset' => true,
                'password_reset_days' => 0, // Força alteração imediata
                'password_changed_at' => $user->password_changed_at ?? $user->created_at
            ]);
            
            // Se for requisição JSON (API), retorna erro específico
            if ($request->wantsJson()) {
                return new JsonResponse([
                    'msg' => $isFirstAccess ? 'Primeiro acesso detectado. É obrigatório alterar a senha.' : 'Senha temporária detectada. É obrigatório alterar a senha.',
                    'require_password_reset' => true,
                    'first_access' => $isFirstAccess,
                    'temporary_password' => $hasTemporaryPassword
                ], 403);
            }
            
            // Redireciona para alteração de senha
            $message = $isFirstAccess ? 'Primeiro acesso detectado. É obrigatório alterar sua senha.' : 'Senha temporária detectada. É obrigatório alterar sua senha.';
            return redirect()->route('alterar-senha.index')->with('warning', $message);
        }
        
        // Se nunca definiu a data de alteração de senha, define agora
        if (is_null($user->password_changed_at)) {
            $user->update(['password_changed_at' => now()]);
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    public function teste(Request $request)
    {
        return view('teste');
    }

    protected function retrieveActiveUser(Request $request): ?User
    {
        return User::withoutGlobalScopes()
            ->where($this->username(), $request->input($this->username()))
            ->where('ativo', true)
            ->first();
    }

    private function formatarDuracao(int $segundos): string
    {
        if ($segundos <= 0) {
            return 'alguns segundos';
        }

        if ($segundos < 60) {
            return $segundos . ' segundo' . ($segundos === 1 ? '' : 's');
        }

        $minutos = (int) ceil($segundos / 60);

        return $minutos . ' minuto' . ($minutos === 1 ? '' : 's');
    }
}
