<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Models\TwoFactorCode;
use App\Models\UsersAndRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController extends Controller
{
    public function confirm2FACode(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'code' => 'required|numeric',
        ]);

        $user = User::where('username', $request->username)->firstOrFail();
        $code = $user->twoFactorCodes()->where('code', $request->code)->first();

        if ($code)
        {
            $code->delete();
            if ($user->tokens()->count() < env('MAX_ACTIVE_TOKENS', 5))
            {
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->expires_at = now()->addMinutes(30);
                $token->save();
                return response()->json([
                    'access_token' => $tokenResult->accessToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $token->expires_at->toDateTimeString()
                ]);
            }
            else
            {
                return response()->json(['message' => 'Авторизовано максимальное количество пользователей'], 429);
            }
        }

        return response()->json(['error' => 'Код неверный'], 422);
    }

    public function resendCode(Request $request)
    {
        $request->validate([
            'username' => 'required',
        ]);

        $user = User::where('username', $request->username)->firstOrFail();

        $lastCode = $user->twoFactorCodes()->orderBy('created_at', 'desc')->first();
        if ($lastCode && $lastCode->created_at->diffInSeconds(now()) < 30) {
            return response()->json(['message' => 'Запросите новый код через 30 секунд'], 429);
        }

        return $this->send2FACode($user);
    }

    private function send2FACode($user)
    {
        $user->twoFactorCodes()->delete();

        $code = TwoFactorCode::generateCode();
        $expiresAt = TwoFactorCode::generateExpiration();

        $user->twoFactorCodes()->create([
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.mail.ru';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hidwidwi@mail.ru';
            $mail->Password   = 'e7mPf9g0jVajcdk2eLSm';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('hidwidwi@mail.ru');
            $mail->addAddress($user->email);

            $mail->isHTML(false);
            $mail->Subject = 'Kod dlya avtorizacii';
            $mail->Body    = $code;

            $mail->send();
            return response()->json(['message' => 'Код двухфакторной авторизации отправлен на ваш email'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Произошла ошибка при отправке сообщения!'], 500);
        }
    }

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

    public function login(AuthLoginRequest $request)
    {
        $userdata = $request->createDTO();

        $user = User::where('email', $userdata->email)->first();

        if (!$user || !Hash::check($userdata->password, $user->password)) {
            return response()->json(['message' => 'Пользователь не авторизован'], 401);
        }

        $lastCode = $user->twoFactorCodes()->orderBy('created_at', 'desc')->first();
        if ($lastCode && $lastCode->created_at->diffInSeconds(now()) < 30) {
            return response()->json(['message' => 'Вы запрашиваете код слишком часто. Попробуйте позже.'], 429);
        }

        $this->send2FACode($user);
        return response()->json(['message' => 'Код двухфакторной авторизации отправлен на ваш email.']);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->delete();

        return response()->json(['message' => 'Вы вышли из аккаунта!'], 200);
    }

    public function logout_all(Request $request)
    {
        $user = $request->user();
        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json(['message' => 'Вы успешно удалили все токены!'], 200);
    }
}
