<?php

namespace App\Http\Requests;

use App\DTO\UpdateUserDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUserRequest extends FormRequest
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
            'username' => 'string|regex:/^[A-Z][a-zA-Z]{6,}$/|unique:users|max:255',
            'email' => 'string|email|max:255|unique:users',
            'password' => 'string|min:8',
            'birthday' => 'date',
        ];
    }

    public function createDTO($userId): UpdateUserDTO
    {
        return new UpdateUserDTO(
            $userId,
            $this->input('username'),
            $this->input('email'),
            $this->input('password'),
            $this->input('birthday'),
            $this->input('created_at'),
        );
    }
}
