<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    //Получение информации об авторизованном пользователе
    public function me(Request $request)
    {
        // Получение текущего авторизованного пользователя из запроса
        $user = $request->user();

        // Возврат информации об авторизованном пользователе в формате JSON
        return response()->json(['user' => $user]);
    }

    //Получение списка токенов авторизованного пользователя
    public function tokens(Request $request)
    {
        // Получение текущего авторизованного пользователя из запроса
        $user = $request->user();

        // Получение списка токенов пользователя
        $tokens = $user->tokens;

        // Возврат списка токенов пользователя в формате JSON
        return response()->json(['tokens' => $tokens]);
    }
}
