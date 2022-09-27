<?php

namespace Edu\Signatrue\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatrueAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, \Closure $next)
    {

        $guard = Auth::guard();
        if (!$guard->validated()) {
            return response()->json([
                'code' => $guard->getErrCode(),
                'err' => $guard->getErrMessage()
            ]);
        }
        return $next($request);
    }
}
