<?php

namespace App\DTO\User;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\DataTransferObject\DataTransferObject;

class RegisterDataDto extends DataTransferObject
{
    public string $name;
    public string $email;
    public string $password;

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password')
        );
    }
}
