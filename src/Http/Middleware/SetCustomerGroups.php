<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy;

class SetCustomerGroups
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (($user = $request->user()) && ! count(GetCandy::getGroups())) {
            // Are we an admin?
            if ($user->hasRole('admin')) {
                $groups = GetCandy::customerGroups()->all();
            } else {
                $groups = $request->user()->groups;
            }
        } else {
            $groups = collect([GetCandy::customerGroups()->getGuest()]);
        }
        GetCandy::setGroups($groups);

        return $next($request);
    }
}
