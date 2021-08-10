<?php

namespace Xguard\LaravelKanban\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Xguard\LaravelKanban\Models\Employee;

class CheckHasAccess
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            if ($employee === null) {
                abort(403, "You need to be added to the kanban. Please ask an admin for access. ");
            }
        } else {
            abort(403, "Please first login to ERP");
        }
        return $next($request);
    }
}
