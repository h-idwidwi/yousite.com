<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    //Аутентификация пользователя
    public function login(AuthLoginRequest $request)
    {
        // Создание DTO из запроса
        $userdata = $request->createDTO();

        // Поиск пользователя по email
        $user = User::where('email', $userdata->email)->first();

        // Проверка правильности пароля и наличия пользователя
        if (!$user || !Hash::check($userdata->password, $user->password)) {
            // Возврат сообщения об ошибке, если пользователь не найден или пароль неверен
            return response()->json(['message' => 'Пользователь не авторизован'], 401);
        }

        // Проверка количества активных токенов пользователя
        $maxTokens = env('MAX_ACTIVE_TOKENS', 5);
        if ($user->tokens()->count() >= $maxTokens) {
            // Возврат сообщения об ошибке, если превышено количество активных токенов
            return response()->json(['message' => 'Превышено количество активных токенов'], 403);
        }

        // Создание токена доступа с временем жизни в одну минуту
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = now()->addMinutes(30);
        $token->save();

        // Возврат успешного ответа с данными токенов
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->expires_at->toDateTimeString()
        ]);
    }


    //Удаление актуального токена
    public function logout(Request $request)
    {
        // Удаление токена пользователя
        $request->user()->token()->delete();

        // Возврат сообщения об успешном выходе
        return response()->json(['message' => 'Вы вышли из аккаунта!'], 200);
    }

    //Удаление всех токенов
    public function logout_all(Request $request)
    {
        // Удаление всех токенов пользователя
        $user = $request->user();
        $user->tokens->each(function ($token) {
            $token->delete();
        });

        // Возврат сообщения об успешном выходе из всех сеансов
        return response()->json(['message' => 'Вы успешно удалили все токены!'], 200);
    }
}
