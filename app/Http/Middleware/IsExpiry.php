<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Token;
use Carbon\Carbon;

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

            $user->twoFactorCodes->each(function ($code) {
                if ($code->expires_at->isPast()) {
                    $code->delete();
                }
                return response()->json(['message' => 'Код истек, запросите новый']);
            });
        }

        return $next($request);
    }
}
