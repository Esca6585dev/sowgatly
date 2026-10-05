<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class AdminLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:admin');
    }

    public function showLoginForm()
    {
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        // Validate the form data
        $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        // Attempt to log the user in
        if(Auth::guard('admin')->attempt(['username' => $request->username, 'password' => $request->password], $request->remember)){
            // If successful, then redirect to their intended location
            return redirect()->intended(route('admin.dashboard', app()->getLocale()));
        }
        // Wrong username or password: say so instead of silently reloading.
        return redirect()->back()
            ->withInput($request->only('username', 'remember'))
            ->withErrors(['username' => __('Wrong username or password.')]);
    }
}
