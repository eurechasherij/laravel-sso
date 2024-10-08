<?php

namespace Zefy\LaravelSSO\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Zefy\LaravelSSO\LaravelSSOBroker;

class SSOReAttach
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
        
        $response = $broker->getUserInfo();

        // If there is a problem with data in SSO server, we will re-attach client session.
        if (isset($response['error']) && strpos($response['error'], 'There is no saved session data associated with the broker session id') !== false) {
            return $this->clearSSOCookie($request);
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
        return redirect($request->fullUrl())->cookie(cookie('sso_token_' . config('laravel-sso.brokerName')));
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
