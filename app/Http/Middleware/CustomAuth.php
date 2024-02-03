<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustomAuth
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $guard=null) 
    {
        try {
            $token = JWTAuth::parseToken()->authenticate();
    
            if (auth()->guard($guard)->check()) {
                return $next($request);
            }
    
            return $this->apiResponse(
                message: "You do not have access to more than that",
                data: null,
                error: true,
                statuscode: 403
            );
    
        } catch (Exception $ex) {
            if ($ex instanceof TokenInvalidException) {

                return $this->apiResponse(
                    data: null,
                    message: "Token Invalid",
                    error: true,
                    statuscode: 400
                );
        
            } else if ($ex instanceof TokenExpiredException) {
                return $this->apiResponse(
                    data: null,
                    message: "Token Expired",
                    error: true,
                    statuscode: 403
                );
            } else {
                return $this->apiResponse(
                    data: null,
                    message: "Token Not Found",
                    error: true,
                    statuscode: 404
                );
            
            }
        }
    }
    
}
