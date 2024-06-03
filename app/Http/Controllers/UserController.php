<?php

namespace App\Http\Controllers;

use App\DTO\UpdateUserDTO;
use App\DTO\UserDTO;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ItIsNotToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\DTO\UserCollectionDTO;
use App\DTO\UserAndRoleCollectionDTO;
use App\Models\UsersAndRoles;
use App\Http\Requests\CreateUserAndRoleRequest;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    // Метод для получения всех пользователей
    public function getUsers(): JsonResponse
    {
        $users = User::all();
        return response()->json(new UserCollectionDTO($users));
    }

    // Метод для получения ролей и разрешений пользователя
    public function getUserRoles(UserRequest $request): JsonResponse
    {
        $user_id = $request->id;
        $user = User::with('roles')->findOrFail($user_id);
        $roles = $user->roles;
        $dto = new UserAndRoleCollectionDTO($roles);
        return response()->json($dto);
    }

    // Метод для назначения роли пользователю
    public function giveUserRoles(CreateUserAndRoleRequest $request, $id): JsonResponse
    {
        $user_id = $id;
        $role_id = $request->input('role_id');
        $exists = UsersAndRoles::where('user_id', $user_id)->where('role_id', $role_id)->exists();
        if ($exists) {
            return response()->json(['status' => 'Такая роль уже назначена'], 409);
        }
        UsersAndRoles::create([
            'user_id' => $user_id,
            'role_id' => $role_id,
            'created_by' => $request->user()->id,
        ]);
        return response()->json(['status' => 'Роль успешно назначена'], 200);
    }

    // Метод для жесткого удаления роли у пользователя
    public function hardDeleteRole($r_id, $id): JsonResponse
    {
        $user_id = $id;
        $role_id = $r_id;

        $userAndRoles = UsersAndRoles::withTrashed()->where('user_id', $user_id)->where('role_id', $role_id)->firstOrFail();

        $userAndRoles->forceDelete();

        return response()->json(['status' => 'Роль пользователя ликвидирована'], 200);
    }

    // Метод для мягкого удаления роли у пользователя
    public function softDeleteRole(UserRequest $request, $r_id, $id): JsonResponse
    {
        $user_id = $id;
        $role_id = $r_id;

        $userAndRoles = UsersAndRoles::where('user_id', $user_id)->where('role_id', $role_id)->firstOrFail();

        $userAndRoles->deleted_by = $request->user()->id;
        $userAndRoles->delete();

        return response()->json(['status' => 'Роль пользователя мягко удалена'], 200);
    }

    // Метод для восстановления мягко удаленной роли у пользователя
    public function restoreDeletedRole(UserRequest $request, $r_id, $id): JsonResponse
    {
        $user_id = $id;
        $role_id = $r_id;

        $userAndRoles = UsersAndRoles::withTrashed()->where('user_id', $user_id)->where('role_id', $role_id)->firstOrFail();

        $userAndRoles->restore();
        $userAndRoles->deleted_by = null;
        $userAndRoles->save();

        return response()->json(['status' => 'Роль пользователя восстановлена'], 200);
    }

    // Метод для получения информации о текущем пользователе
    public function me(UserRequest $request): JsonResponse
    {
        $user = $request->user();

        $userDTO = new UserDTO(
            $user->id,
            $user->username,
            $user->email,
            $user->birthday,
            $user->created_at
        );

        return response()->json(["user" => $userDTO]);
    }

    // Метод для получения токенов текущего пользователя
    public function tokens(Request $request)
    {
        $user = $request->user();
        $encryptedTokens = ItIsNotToken::where('user_id', $user->id)->get();

        $tokens = $encryptedTokens->map(function ($token) {
            return $this->decrypt($token->it_is_not_token, $this->encryptionKey);
        });

        return response()->json(['tokens' => $tokens]);
    }
    protected $encryptionKey = 5;

    private function decrypt($encryptedString, $key)
    {
        $decodedString = base64_decode($encryptedString);
        $decrypted = '';
        foreach (str_split($decodedString) as $char) {
            $decrypted .= chr(ord($char) - $key);
        }
        return $decrypted;
    }
    // Метод для обновления информации о пользователе
    public function updateUser(UpdateUserRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $userDTO = $request->createDTO($user->id);

        $updateData = array_filter([
            'username' => $userDTO->username,
            'email' => $userDTO->email,
            'password' => $userDTO->password ? bcrypt($userDTO->password) : null,
            'birthday' => $userDTO->birthday,
        ], function($value) {
            return !is_null($value);
        });

        $user->update($updateData);
        return response()->json(new UpdateUserDTO($user->id, $user->username, $user->email, $user->password, $user->birthday, $user->created_at), 201);
    }

    public function hardDeleteUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();
        return response()->json('Пользователь ликвидирован', 201);
    }
    public function softDeleteUser($id, Request $request)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->deleted_by = $request->user()->id;
        $user->save();
        $user->deleted_at = Carbon::now();
        $user->delete();
        return response()->json('Пользователь мягко удален', 201);
    }
    public function restoreDeletedUser($id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return response()->json(new UserDTO($user->id, $user->username, $user->email, $user->birthday, $user->created_at), 201);
    }
}
