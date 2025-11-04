<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="MessageResource",
 *     type="object",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Logged out",
 *         description="Сообщение о результате операции"
 *     )
 * )
 */
class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->resource['message'],
        ];
    }
}
