<?php

namespace Zefy\LaravelSSO\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Zefy\LaravelSSO\LaravelSSOBroker;

class SSOAutoLogin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $broker = new LaravelSSOBroker();
        
        $response = Cache::remember('broker.userInfo.'.$request->cookie('sso_token_' . config('laravel-sso.brokerName')), config('laravel-sso.ssoCacheTime', 60), function () use ($broker) {
            return $broker->getUserInfo();
        });

        // If client is logged out in SSO server but still logged in broker.
        if (!isset($response['data']) && !auth()->guest()) {
            Cache::forget('broker.userInfo.'.$request->cookie('sso_token_' . config('laravel-sso.brokerName')));
            return $this->logout($request);
        }

        // If there is a problem with data in SSO server, we will re-attach client session.
        if (isset($response['error']) && strpos($response['error'], 'There is no saved session data associated with the broker session id') !== false) {
            Cache::forget('broker.userInfo.'.$request->cookie('sso_token_' . config('laravel-sso.brokerName')));
            return $this->clearSSOCookie($request);
        }

        // If client is logged in SSO server and didn't logged in broker...
        if (isset($response['data']) && (auth()->guest() || auth()->user()->id != $response['data']['id'])) {
            // ... we will authenticate our client.
            Cache::forget('broker.userInfo.'.$request->cookie('sso_token_' . config('laravel-sso.brokerName')));
            auth()->loginUsingId($response['data']['id']);
        }

        if (isset($response['error'])) {
            Cache::forget('broker.userInfo.'.$request->cookie('sso_token_' . config('laravel-sso.brokerName')));
        }

        return $next($request);
    }

    /**
     * Clearing SSO cookie so broker will re-attach SSO server session.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function clearSSOCookie(Request $request)
    {
        return redirect($request->fullUrl())->cookie($request->cookie('sso_token_' . config('laravel-sso.brokerName')));
    }

    /**
     * Logging out authenticated user.
     * Need to make a page refresh because current page may be accessible only for authenticated users.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function logout(Request $request)
    {
        auth()->logout();
        return redirect($request->fullUrl());
    }
}
