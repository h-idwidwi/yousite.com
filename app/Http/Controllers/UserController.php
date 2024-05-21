<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DTO\UserCollectionDTO;
use App\DTO\UserAndRoleCollectionDTO;
use App\Models\UsersAndRoles;
use App\Models\Role;
use App\DTO\RoleCollectionDTO;
use App\Http\Requests\ChangeUserAndRoleRequest;
use App\Http\Requests\CreateUserAndRoleRequest;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    public function getUsers(Request $request) {
        $users = new UserCollectionDTO();
        return response()->json($users->users);
    }

    public function getUserRoles(UserRequest $request) {

        $user_id = $request->id;

        $roles_id = UsersAndRoles::select('role_id')->where('user_id', $user_id)->get();

        $roles = $roles_id->map(function($id) {
            return Role::where('id', $id->role_id)->first();
        });

        return response()->json($roles);
    }

    public function giveUserRoles(CreateUserAndRoleRequest $request) {

        $user_id = $request->id;

        $role_id = $request->input('role_id');

        $count = UsersAndRoles::where('user_id', $user_id)->where('role_id', $role_id)->count();

        if ($count) {
            return response()->json(['status' => '501']);
        }

        UsersAndRoles::create([
            'user_id' => $user_id,
            'role_id' => $role_id,
            'created_by' => $request->user()->id,
        ]);
        return response()->json(['status' => '200']);

    }

    public function hardDeleteRole(ChangeUserAndRoleRequest $request) {
        $user_id = $request->id;
        $role_id = $request->role_id;

        $userAndRoles = UsersAndRoles::withTrashed()->where('user_id', $user_id)->where('role_id', $role_id);

        $userAndRoles->forcedelete();

        return response()->json(['status' => '200']);
    }

    public function softDeleteRole(ChangeUserAndRoleRequest $request){

        $user_id = $request->id;
        $role_id = $request->role_id;

        $userAndRoles = UsersAndRoles::where('user_id', $user_id)->where('role_id', $role_id)->first();

        $userAndRoles->deleted_by = $request->user()->id;
        $userAndRoles->delete();
        $userAndRoles->save();

        return response()->json(['status' => '200']);
    }

    public function restoreDeletedRole(ChangeUserAndRoleRequest $request) {
        $user_id = $request->id;
        $role_id = $request->role_id;

        $userAndRoles = UsersAndRoles::withTrashed()->where('user_id', $user_id)->where('role_id', $role_id)->first();

        $userAndRoles->restore();
        $userAndRoles->deleted_by = null;
        $userAndRoles->save();

        return response()->json(['status' => '200']);
    }
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json(["user" => $user]);
    }

    public function tokens(Request $request) {
        $user = $request->user();
        $tokens = $user->tokens;

        return response()->json(['tokens' => $tokens]);
    }
}
