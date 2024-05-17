<?php

namespace App\Http\Requests;

use App\DTO\RegisterDTO;
use Illuminate\Foundation\Http\FormRequest;

class AuthRegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|regex:/^[A-Z][a-zA-Z]{6,}$/|unique:users,username|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
            ],
            'birthday' => 'required|date',
        ];
    }

    public function createDTO(): RegisterDTO
    {
        return new RegisterDTO(
            $this->input('username'),
            $this->input('email'),
            $this->input('password'),
            $this->input('birthday')
        );
    }
}

