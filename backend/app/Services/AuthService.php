<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\User\LoginDataDto;
use App\DTO\User\RegisterDataDto;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Авторизация пользователя
     *
     * @param LoginDataDto $data
     * @return array
     * @throws ValidationException
     */
    public function login(LoginDataDto $data): array
    {
        $user = User::where('email', $data->email)->first();

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['token' => $token];
    }

    /**
     * Регистрация пользователя
     *
     * @param RegisterDataDto $data
     * @return array
     */
    public function register(RegisterDataDto $data): array
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['token' => $token];
    }

    /**
     * Выход пользователя (отзыв всех токенов)
     *
     * @param User $user
     * @return array
     */
    public function logout(User $user): array
    {
        $user->tokens()->delete();

        return ['message' => 'Logged out'];
    }

    /**
     * Выход пользователя из текущего устройства (отзыв текущего токена)
     *
     * @param User $user
     * @return array
     */
    public function logoutCurrentDevice(User $user): array
    {
        $user->currentAccessToken()->delete();

        return ['message' => 'Logged out from current device'];
    }

    /**
     * Получение текущего пользователя
     *
     * @param User $user
     * @return User
     */
    public function getCurrentUser(User $user): User
    {
        return $user->loadMissing('counterparties');
    }

    /**
     * Обновление токена (refresh)
     *
     * @param User $user
     * @return array
     */
    public function refreshToken(User $user): array
    {
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return ['token' => $token];
    }
}
