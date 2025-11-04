<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CounterpartyResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="inn", type="string", example="7707083893"),
 *     @OA\Property(property="name", type="string", example="ООО Ромашка"),
 *     @OA\Property(property="ogrn", type="string", example="1027700132195"),
 *     @OA\Property(property="address", type="string", example="г. Москва, ул. Ленина, 1"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-04T12:34:56+00:00"),
 *     description="Counterparty resource"
 * )
 */
class CounterpartyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'inn' => $this->inn,
            'name' => $this->name,
            'ogrn' => $this->ogrn,
            'address' => $this->address,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
