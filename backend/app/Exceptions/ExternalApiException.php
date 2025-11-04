<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExternalApiException extends Exception
{
    public function __construct(
        string $messageKey = 'external_api.error',
        private readonly int $status = 502,
        private readonly array $replace = []
    ) {
        // Сообщение переводится на лету
        parent::__construct(__($messageKey, $replace), $status);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->status);
    }
}
