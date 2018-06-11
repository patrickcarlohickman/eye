<?php

namespace Eyewitness\Eye\Http\Controllers;

use Eyewitness\Eye\Eye;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    /**
     * Get login page.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if ($request->session()->has('eyewitness:auth') || Eye::check($request)) {
            return redirect(route('eyewitness.dashboard').'#overview');
        }

        if (config('eyewitness.login_disabled', false)) {
            return redirect('/');
        }

        return view('eyewitness::login');
    }

    /**
     * Attempt to authenticate.
     *
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        if (config('eyewitness.login_disabled', false)) {
            return redirect('/');
        }

        if (! app(Eye::class)->checkConfig()) {
            return redirect()->route('eyewitness.login')->withError('Eyewitness has not been configured correctly. Login has been disabled for your security. Please run "php artisan eyewitness:debug" to determine the issue.');
        }

        if ($request->app_token !== config('eyewitness.app_token') ||
            $request->secret_key !== config('eyewitness.secret_key')) {
            return redirect()->route('eyewitness.login')->withError('Incorrect credentials.');
        }

        $request->session()->put('eyewitness:auth', 1);

        return redirect(route('eyewitness.dashboard').'#overview');
    }

    /**
     * Logout from Eyewitness.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->session()->forget('eyewitness:auth');

        return redirect()->route('eyewitness.login')->withSuccess('You have been logged out.');
    }
}
