<?php

namespace App\Http\Requests;

use App\DTO\RoleDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $this->role->id,
            'code' => 'required|string|max:255|unique:roles,code,' . $this->role->id,
            'description' => 'nullable|string',
        ];
    }

    public function createDTO()
    {
        return new RoleDTO(
            $this->role->id,
            $this->input('name'),
            $this->input('code'),
            $this->input('description')
        );
    }
}
