<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegisterRequest;
use App\Models\User;
use App\Models\UsersAndRoles;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function register(AuthRegisterRequest $request) {
        // Проверка аутентификации
        if (Auth::check()) {
            return response()->json(['message' => 'Вы уже авторизованы'], 403);
        }

        $userData = $request->createDTO();

        $user = User::create([
            'username' => $userData->username,
            'email' => $userData->email,
            'password' => bcrypt($userData->password),
            'birthday' => $userData->birthday,
        ]);

        UsersAndRoles::create([
            'user_id' => $user->id,
            'role_id' => 3,
            'created_by' => 1,
        ]);

        return response()->json($user, Response::HTTP_CREATED);
    }
}
