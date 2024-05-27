<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Token;

class IsExpiry
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            $token = $user->token();

            if ($token->expires_at->isPast()) {
                $user->tokens->each(function ($token) {
                    $token->delete();
                });
                return response()->json(['message' => 'Токен истек, залогиньтесь заново'], 401);
            }
        }
        return $next($request);
    }
}
