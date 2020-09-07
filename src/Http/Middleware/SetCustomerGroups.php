<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;
use GetCandy\Api\Core\Customers\Actions\FetchDefaultCustomerGroup;

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
                $groups = FetchCustomerGroups::run([
                    'paginate' => false,
                ]);
            } else if ($request->user()->customer) {
                $groups = $request->user()->customer->customerGroups;
            } else {
                $groups = collect([FetchDefaultCustomerGroup::run()]);
            }
        }
        GetCandy::setGroups($groups);

        return $next($request);
    }
}
