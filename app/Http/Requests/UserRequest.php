<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserRequest extends FormRequest
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
        return [];
    }
}
