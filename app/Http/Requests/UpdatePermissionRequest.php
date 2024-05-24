<?php

namespace App\Http\Requests;

use App\DTO\RoleDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePermissionRequest extends FormRequest
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
            'name' => 'string|max:255|unique:permissions,name,',
            'code' => 'string|max:255|unique:permissions,code,',
            'description' => 'nullable|string',
        ];
    }

    public function createDTO($permissionId)
    {
        return new RoleDTO(
            $permissionId,
            $this->input('name'),
            $this->input('description'),
            $this->input('code'),
            $this->input('created_by'),
            $this->input('deleted_by'),
        );
    }
}
