<?php

declare(strict_types=1);

namespace App\Http\Requests\Counterparty;

use App\Http\Requests\AbstractApiRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CreateCounterpartyRequest",
 *     type="object",
 *     required={"inn"},
 *     @OA\Property(
 *         property="inn",
 *         type="string",
 *         example="7707083893",
 *         description="ИНН контрагента (10 или 12 цифр)"
 *     ),
 *     description="Request for creating counterparty by INN"
 * )
 */
class CreateCounterpartyRequest extends AbstractApiRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'inn' => ['required', 'string', 'regex:/^(?:\d{10}|\d{12})$/', 'unique:counterparties,inn'],
        ];
    }

    public function messages(): array
    {
        return [
            'inn.required' => 'ИНН обязателен.',
            'inn.regex'    => 'ИНН должен содержать 10 или 12 цифр.',
            'inn.unique'   => 'ИНН уже существует',
        ];
    }
}
