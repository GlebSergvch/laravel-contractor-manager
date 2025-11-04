<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TokenResource",
 *     type="object",
 *     @OA\Property(
 *         property="access_token",
 *         type="string",
 *         example="1|abcdef1234567890",
 *         description="JWT токен для доступа к API"
 *     ),
 *     @OA\Property(
 *         property="token_type",
 *         type="string",
 *         example="Bearer",
 *         description="Тип токена"
 *     )
 * )
 */
class TokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource['token'],
            'token_type' => 'Bearer',
        ];
    }
}
