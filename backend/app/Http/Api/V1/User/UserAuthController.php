<?php

declare(strict_types=1);

namespace App\Http\Api\V1\User;

use App\DTO\User\LoginDataDto;
use App\DTO\User\RegisterDataDto;
use App\Http\Api\V1\AbstractController;
use App\Http\Requests\UserAuth\LoginRequest;
use App\Http\Requests\UserAuth\RegisterRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\TokenResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAuthController extends AbstractController
{
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * @OA\Post(
     *      path="/api/v1/login",
     *      summary="Авторизация пользователя",
     *      tags={"Auth"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Успешная авторизация",
     *          @OA\JsonContent(ref="#/components/schemas/TokenResource")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Ошибка валидации",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Неверные учетные данные",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid credentials"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login(LoginDataDto::fromRequest($request));

        return response()->json(new TokenResource($token));
    }

    /**
     * @OA\Post(
     *      path="/api/v1/register",
     *      summary="Регистрация пользователя",
     *      tags={"Auth"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Успешная регистрация",
     *          @OA\JsonContent(ref="#/components/schemas/TokenResource")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Ошибка валидации",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $token = $this->authService->register(RegisterDataDto::fromRequest($request));

        return response()->json(new TokenResource($token), 201);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/logout",
     *      summary="Выход пользователя",
     *      tags={"Auth"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Успешный выход",
     *          @OA\JsonContent(ref="#/components/schemas/MessageResource")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Неавторизован",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $message = $this->authService->logout($request->user());

        return response()->json(new MessageResource($message));
    }
}
