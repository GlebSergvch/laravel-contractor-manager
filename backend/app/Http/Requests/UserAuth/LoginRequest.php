<?php

// app/Http/Requests/LoginRequest.php
declare(strict_types=1);

namespace App\Http\Requests\UserAuth;

use App\Http\Requests\AbstractApiRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="john@example.com",
 *         description="Email пользователя"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         example="secret123",
 *         description="Пароль пользователя"
 *     ),
 *     description="Данные для авторизации пользователя"
 * )
 */
class LoginRequest extends AbstractApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }
}
