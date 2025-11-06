<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            auth()->setUser($user);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token expired'], 401);

        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token invalid'], 401);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Token not provided'], 401);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return $next($request);
    }
}
