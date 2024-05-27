<?php

namespace App\Http\Requests;

use App\DTO\RoleDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (Auth::check()) {
            return true;
        }
        return false;
    }

    public function rules()
    {
        return [
            'name' => 'string|max:255|unique:roles,name,',
            'code' => 'string|max:255|unique:roles,code,',
            'description' => 'nullable|string',
        ];
    }

    public function createDTO($roleId)
    {
        return new RoleDTO(
            $roleId,
            $this->input('name'),
            $this->input('description'),
            $this->input('code'),
            $this->input('created_by'),
            $this->input('deleted_by'),
        );
    }
}
