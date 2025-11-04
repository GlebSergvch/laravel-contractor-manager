<?php

declare(strict_types=1);

namespace App\DTO\Counterparty;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\DataTransferObject\DataTransferObject;

class CreateCounterpartyDto extends DataTransferObject
{
    public string $inn;

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            inn: (string) $request->input('inn'),
        );
    }
}
