<?php

namespace Eyewitness\Eye\Test\Controllers;

use Eyewitness\Eye\Eye;
use Eyewitness\Eye\Test\TestCase;

class AuthControllerTest extends TestCase
{

    public function test_login_page_loads()
    {
        $response = $this->get($this->home);

        $response->assertStatus(200);
    }

    public function test_login_page_redirects_if_disabled()
    {
        $this->app['config']->set('eyewitness.login_disabled', true);

        $response = $this->get($this->home);

        $response->assertRedirect('/');
        $response->assertSessionMissing('eyewitness:auth');
    }

    public function test_login_page_redirects_to_dashboard_if_logged_in()
    {
        $response = $this->withSession(['eyewitness:auth' => 1])
                         ->get($this->home);

        $response->assertRedirect($this->home.'/dashboard#overview');
    }

    public function test_logout_page_redirects_to_login_page()
    {
        $response = $this->withSession(['eyewitness:auth' => 1])
                         ->post($this->home.'/logout');

        $response->assertRedirect($this->home);
        $response->assertSessionMissing('eyewitness:auth');
        $response->assertSessionHas('success', 'You have been logged out.');
    }

    public function test_authenticate_requires_correctly_configured_installation()
    {
        $this->app['config']->set('eyewitness.app_token', '');

        $response = $this->post($this->home,
                            ['app_token' => config('eyewitness.app_token'),
                             'secret_key' => config('eyewitness.secret_key')]);

        $response->assertRedirect($this->home);
        $response->assertSessionMissing('eyewitness:auth');
        $response->assertSessionHas('error', 'Eyewitness has not been configured correctly. Login has been disabled for your security. Please run "php artisan eyewitness:debug" to determine the issue.');
    }

    public function test_authenticate_requires_correct_details()
    {
        $response = $this->post($this->home,
                            ['app_token' => config('eyewitness.app_token'),
                             'secret_key' => 'wrong']);

        $response->assertRedirect($this->home);
        $response->assertSessionMissing('eyewitness:auth');
        $response->assertSessionHas('error', 'Incorrect credentials.');
    }

    public function test_authenticate_accepts_correct_details_and_logs_in()
    {
        $response = $this->post($this->home,
                            ['app_token' => config('eyewitness.app_token'),
                             'secret_key' => config('eyewitness.secret_key')]);

        $response->assertRedirect($this->home.'/dashboard#overview');
        $response->assertSessionHas('eyewitness:auth', 1);
    }

    public function test_authenticate_wont_work_if_login_disabled()
    {
        $this->app['config']->set('eyewitness.login_disabled', true);

        $response = $this->post($this->home,
                            ['app_token' => config('eyewitness.app_token'),
                             'secret_key' => config('eyewitness.secret_key')]);

        $response = $this->get($this->home);

        $response->assertRedirect('/');
        $response->assertSessionMissing('eyewitness:auth');
    }

    public function test_closure_auth_succeds()
    {
        Eye::auth(function ($request) {
            return true;
        });

        $response = $this->get($this->home);

        $response->assertSessionMissing('eyewitness:auth');
        $response->assertRedirect($this->home.'/dashboard#overview');
    }

    public function test_closure_auth_succeds_with_login_disabled()
    {
        $this->app['config']->set('eyewitness.login_disabled', true);

        Eye::auth(function ($request) {
            return true;
        });

        $response = $this->get($this->home);

        $response->assertSessionMissing('eyewitness:auth');
        $response->assertRedirect($this->home.'/dashboard#overview');
    }

    public function test_closure_auth_fails()
    {
        Eye::auth(function ($request) {
            return false;
        });

        $response = $this->get($this->home.'/dashboard');

        $response->assertRedirect($this->home);
        $response->assertSessionMissing('eyewitness:auth');
        $response->assertSessionHas('warning', 'Sorry - you must login to Eyewitness first.');
    }
}
