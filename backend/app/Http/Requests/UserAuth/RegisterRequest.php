<?php

// app/Http/Requests/RegisterRequest.php
declare(strict_types=1);

namespace App\Http\Requests\UserAuth;

use App\Http\Requests\AbstractApiRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"name", "email", "password"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="John Doe",
 *         description="ФИО пользователя"
 *     ),
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
 *         description="Пароль пользователя (мин. 6 символов)"
 *     ),
 *     description="Данные для регистрации пользователя"
 * )
 */
class RegisterRequest extends AbstractApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ];
    }
}
