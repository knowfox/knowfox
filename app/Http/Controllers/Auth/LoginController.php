<?php

namespace Knowfox\Http\Controllers\Auth;

use Knowfox\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Knowfox\Models\EmailLogin;
use Knowfox\Jobs\SendLoginMail;


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
    protected $redirectTo = '/concepts';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function login(Request $request)
    {
        $this->validate($request, ['email' => 'required|email|exists:users']);

        $email_login = EmailLogin::createForEmail($request->input('email'));
        $user = $email_login->user()->first();

        $url = route('auth.email-authenticate', [
            'token' => $email_login->token
        ]);

        $this->dispatch(new SendLoginMail($user, $url));

        // show the users a view saying "check your email"
        return redirect('/')
            ->with('message', 'We have sent you an email. It contains a link for you to login.');
    }

    public function authenticateEmail($token, $cid = null)
    {
        $emailLogin = EmailLogin::validFromToken($token);

        Auth::login($emailLogin->user, /*remember*/true);

        if ($cid) {
            return redirect()->route('concept.show', [$cid]);
        }
        else {
            return redirect()->route('home');
        }
    }
}
