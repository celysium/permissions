<?php

namespace Celysium\Permission\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param $role
     * @return Response|RedirectResponse
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (Gate::allows('role', $role)) {
            return $next($request);
        }

        throw new AuthorizationException();
    }
}
