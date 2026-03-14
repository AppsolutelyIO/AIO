<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers\Auth;

use Appsolutely\AIO\Http\Controllers\BaseController;
use Appsolutely\AIO\Models\Model;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class RegisteredUserController extends BaseController
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $userModel = Model::userModel();

        Auth::login($user = $userModel::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]));

        event(new Registered($user));

        return redirect('dashboard');
    }
}
