<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\DTO\UserAndRoleDTO;

class CreateUserAndRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (Auth::check()) {
            return true;
        }
        return false;
    }
    public function rules(): array
    {
        return [
            'role_id' => 'required',
        ];
    }

    public function createDTO(): UserAndRoleDTO
    {
        return new UserAndRoleDTO(
            $this->input('user_id'),
            $this->input('role_id'),
            $this->input('deleted_by'),
            $this->input('created_by')
        );
    }

}
