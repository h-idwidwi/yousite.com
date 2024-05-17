<?php

namespace App\Http\Requests;

use App\DTO\AuthDTO;
use Illuminate\Foundation\Http\FormRequest;

class AuthLoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:255',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
            ],
        ];
    }

    public function createDTO(): AuthDTO
    {
        return new AuthDTO(
            $this->input('email'),
            $this->input('password')
        );
    }
}
