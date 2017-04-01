<?php
//
//namespace App\Http\Middleware;
//
//use Closure;
//use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
//
//class RoleMiddleware extends BaseMiddleware
//{
//    const DELIMITER = '|';
//
//    /**
//     * Handle an incoming request.
//     *
//     * @param  \Illuminate\Http\Request $request
//     * @param  \Closure $next
//     * @param  $roles
//     * @return mixed
//     */
//    public function handle($request, Closure $next, $roles)
//    {
//        if (!is_array($roles)) {
//            $roles = explode(self::DELIMITER, $roles);
//        }
//        if (!$request->user()->hasRole($roles)) {
//            return response()->json(['error' => 'forbidden_action'], 403, [
//                    'Access-Control-Allow-Origin' => '*',
//                    'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
//                    'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin']
//            );
//        }
//
//        return $next($request);
//    }
//}
