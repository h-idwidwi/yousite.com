<?php

namespace App\Http\Requests;

use App\DTO\RoleCreateDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateRoleRequest extends FormRequest
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
            'name' => 'required|unique:Roles',
            'code' => 'required|unique:Roles',
            'description' => 'required|unique:Roles',
        ];
    }

    public function createDTO(): RoleCreateDTO
    {
        return new RoleCreateDTO(
            $this->input('name'),
            $this->input('description'),
            $this->input('code'),
        );
    }
}
