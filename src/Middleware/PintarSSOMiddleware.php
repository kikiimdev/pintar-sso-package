<?php

namespace Banjarmasinkota\PintarSSO\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Banjarmasinkota\PintarSSO\PintarSSO;
use Illuminate\Support\Facades\Auth;

class PintarSSOMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) return $next($request);

        $pintar_account = $user->pintar_account;
        if (!$pintar_account) return $next($request);

        $sso = new PintarSSO();
        $sso->log_activity($request);

        return $next($request);
    }
}
