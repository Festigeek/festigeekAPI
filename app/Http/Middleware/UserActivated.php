<?php

    namespace App\Http\Middleware;

    use Closure;
    use JWTAuth;
    use Illuminate\Http\Response;

    class UserActivated {

        /**
         * Handle an incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure  $next
         * @return mixed
         */
        public function handle($request, Closure $next)
        {
            $response = $next($request);

            $user = JWTAuth::user();

            if (!$user->activated)
            {
                return response()->json(['error' => 'non_active_account'], 401, [
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
                    'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin']
                );
            }

            return $response;
        }
    }