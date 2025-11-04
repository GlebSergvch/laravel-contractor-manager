<?php

namespace App\DTO\User;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\DataTransferObject\DataTransferObject;

class LoginDataDto extends DataTransferObject
{
    public string $email;
    public string $password;

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            email: $request->input('email'),
            password: $request->input('password')
        );
    }
}
