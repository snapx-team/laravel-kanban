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
            $employee = Employee::where('email', '=', Auth::user()->email)->first();
            if ($employee === null || $employee->role !== "admin") {
                abort(403, "You need to be an admin to view this page");
            }
        }
        else{
            abort(403, "Please first login to ERP");
        }
        return $next($request);
    }
}
