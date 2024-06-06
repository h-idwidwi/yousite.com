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
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $changeLogsController;

    public function __construct(ChangeLogsController $changeLogsController)
    {
        $this->changeLogsController = $changeLogsController;
    }

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
        $user = $request->user();
        $exists = UsersAndRoles::where('user_id', $user_id)->where('role_id', $role_id)->exists();
        if ($exists) {
            return response()->json(['status' => 'Такая роль уже назначена'], 409);
        }

        DB::beginTransaction();
        try {
            $newRole = UsersAndRoles::create([
                'user_id' => $user_id,
                'role_id' => $role_id,
                'created_by' => $user->id,
            ]);

            $this->changeLogsController->createLogs('users_and_roles', $newRole->id, null, $newRole, $user->id);
            DB::commit();

            return response()->json(['status' => 'Роль успешно назначена'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Метод для жесткого удаления роли у пользователя
    public function hardDeleteRole($r_id, $id): JsonResponse
    {
        $user = request()->user();
        $user_id = $id;
        $role_id = $r_id;

        $userAndRoles = UsersAndRoles::withTrashed()->where('user_id', $user_id)->where('role_id', $role_id)->firstOrFail();

        DB::beginTransaction();
        try {
            $before = $userAndRoles->replicate();
            $userAndRoles->forceDelete();
            $this->changeLogsController->createLogs('users_and_roles', $userAndRoles->id, $before, null, $user->id);
            DB::commit();

            return response()->json(['status' => 'Роль пользователя ликвидирована'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Метод для мягкого удаления роли у пользователя
    public function softDeleteRole(UserRequest $request, $r_id, $id): JsonResponse
    {
        $user = $request->user();
        $user_id = $id;
        $role_id = $r_id;

        $userAndRoles = UsersAndRoles::where('user_id', $user_id)->where('role_id', $role_id)->firstOrFail();

        DB::beginTransaction();
        try {
            $before = $userAndRoles->replicate();
            $userAndRoles->deleted_by = $user->id;
            $userAndRoles->delete();
            $this->changeLogsController->createLogs('users_and_roles', $userAndRoles->id, $before, $userAndRoles, $user->id);
            DB::commit();

            return response()->json(['status' => 'Роль пользователя мягко удалена'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Метод для восстановления мягко удаленной роли у пользователя
    public function restoreDeletedRole(UserRequest $request, $r_id, $id): JsonResponse
    {
        $user = $request->user();
        $user_id = $id;
        $role_id = $r_id;

        $userAndRoles = UsersAndRoles::withTrashed()->where('user_id', $user_id)->where('role_id', $role_id)->firstOrFail();

        DB::beginTransaction();
        try {
            $before = $userAndRoles->replicate();
            $userAndRoles->restore();
            $userAndRoles->deleted_by = null;
            $userAndRoles->save();
            $this->changeLogsController->createLogs('users_and_roles', $userAndRoles->id, $before, $userAndRoles, $user->id);
            DB::commit();

            return response()->json(['status' => 'Роль пользователя восстановлена'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
        $tokens = $user->tokens->map(function ($token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'scopes' => $token->scopes,
                'revoked' => $token->revoked,
                'created_at' => $token->created_at,
                'updated_at' => $token->updated_at,
                'expires_at' => $token->expires_at,
            ];
        });
        return response()->json(['tokens' => $tokens]);
    }
    // Метод для обновления информации о пользователе
    public function updateUser(UpdateUserRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $userDTO = $request->createDTO($user->id);
        $userRequestUser = $request->user();

        $updateData = array_filter([
            'username' => $userDTO->username,
            'email' => $userDTO->email,
            'password' => $userDTO->password ? bcrypt($userDTO->password) : null,
            'birthday' => $userDTO->birthday,
        ], function($value) {
            return !is_null($value);
        });

        DB::beginTransaction();
        try {
            $before = $user->replicate();
            $user->update($updateData);
            $this->changeLogsController->createLogs('users', $user->id, $before, $user, $userRequestUser->id);
            DB::commit();

            return response()->json(new UpdateUserDTO($user->id, $user->username, $user->email, $user->password, $user->birthday, $user->created_at), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
