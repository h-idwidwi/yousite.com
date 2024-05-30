<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Models\ItIsNotToken;
use App\Models\UsersAndRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(AuthRegisterRequest $request)
    {

        $userData = $request->createDTO();

        $user = User::create([
            'username' => $userData->username,
            'email' => $userData->email,
            'password' => bcrypt($userData->password),
            'birthday' => $userData->birthday,
        ]);

        UsersAndRoles::create([
            'user_id' => $user->id,
            'role_id' => 2,
            'created_by' => 1,
        ]);

        return response()->json($user, 201);
    }

    //Аутентификация пользователя
    protected $encryptionKey = 5;

    public function login(AuthLoginRequest $request)
    {
        $userdata = $request->createDTO();

        $user = User::where('email', $userdata->email)->first();

        if (!$user || !Hash::check($userdata->password, $user->password)) {
            return response()->json(['message' => 'Пользователь не авторизован'], 401);
        }

        $maxTokens = env('MAX_ACTIVE_TOKENS', 5);
        if ($user->tokens()->count() >= $maxTokens) {
            $user->tokens()->delete();
            return response()->json(['message' => 'Превышено количество активных токенов, залогиньтесь заново'], 403);
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = now()->addMinutes(30);
        $token->save();
        $encryptedToken = $this->encrypt($tokenResult->accessToken, $this->encryptionKey);

        ItIsNotToken::create([
            'user_id' => $user->id,
            'token_id' => $token->id,
            'it_is_not_token' => $encryptedToken,
        ]);

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->expires_at->toDateTimeString()
        ]);
    }

    private function encrypt($string, $key)
    {
        $encrypted = '';
        foreach (str_split($string) as $char) {
            $encrypted .= chr(ord($char) + $key);
        }
        return base64_encode($encrypted);
    }

    //Удаление актуального токена
    public function logout(Request $request)
    {
        $user = $request->user();
        $token = $user->token();
        ItIsNotToken::where('token_id', $token->id)->delete();
        $token->delete();
        return response()->json(['message' => 'Вы вышли из аккаунта!'], 200);
    }

    //Удаление всех токенов
    public function logout_all(Request $request)
    {
        $user = $request->user();
        $user->tokens->each(function ($token) {
            ItIsNotToken::where('token_id', $token->id)->delete();
            $token->delete();
        });
        return response()->json(['message' => 'Вы успешно удалили все токены!'], 200);
    }
}
