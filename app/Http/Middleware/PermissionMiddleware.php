<?php
//
//namespace App\Http\Middleware;
//
//use Closure;
//use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
//
//class PermissionMiddleware extends BaseMiddleware
//{
//    const DELIMITER = '|';
//
//    /**
//     * Handle an incoming request.
//     *
//     * @param  \Illuminate\Http\Request $request
//     * @param  \Closure $next
//     * @param  $permissions
//     * @return mixed
//     */
//    public function handle($request, Closure $next, $permissions)
//    {
//        if (!is_array($permissions)) {
//            $permissions = explode(self::DELIMITER, $permissions);
//        }
//        if (!$request->user()->can($permissions)) {
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
