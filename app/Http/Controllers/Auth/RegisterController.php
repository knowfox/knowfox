<?php

namespace Knowfox\Http\Controllers\Auth;

use Knowfox\User;
use Knowfox\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => [
                'required',
                'max:255',
                'regex:/^[-\w\s,\.]+$/u',
            ],
            'email' => 'required|email|max:255|unique:users',
        ], [
            'name.required' => 'Please give us your name',
            'name.regex' => 'Only letters, digits, blanks, dashes, commas, or dots are allowed',
            'email.required' => 'We need your email address for login',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $this->dispatch(new SendRegisterMail($user));

        return $user;
    }
}
