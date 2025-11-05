<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Support\Facades\Http;

/**
 * Простые вспомогательные методы для подмены ответов DaData в тестах.
 */
trait FakesDaData
{
    /**
     * Подставляет успешный ответ для конкретного INN.
     */
    protected function fakeDaDataSuccessForInn(string $inn, array $data): void
    {
        Http::fake(function ($request) use ($inn, $data) {
            $body = json_decode($request->body(), true) ?: [];
            $query = $body['query'] ?? null;

            if ($query === $inn) {
                $payload = [
                    'suggestions' => [
                        [
                            'value' => $data['name'] ?? $inn,
                            'data' => [
                                'name' => ['short_with_opf' => $data['name'] ?? $inn],
                                'ogrn' => $data['ogrn'] ?? null,
                                'address' => ['unrestricted_value' => $data['address'] ?? null],
                            ],
                        ],
                    ],
                ];

                return Http::response($payload, 200, ['Content-Type' => 'application/json']);
            }

            return Http::response(['suggestions' => []], 200, ['Content-Type' => 'application/json']);
        });
    }

    /**
     * Подставляет "not found" (пустой suggestions) для любых запросов.
     */
    protected function fakeDaDataNotFound(): void
    {
        Http::fake([
            '*' => Http::response(['suggestions' => []], 200, ['Content-Type' => 'application/json']),
        ]);
    }

    /**
     * Подставляет ошибку внешнего сервиса (по коду).
     */
    protected function fakeDaDataError(int $status = 500): void
    {
        Http::fake([
            '*' => Http::response([], $status),
        ]);
    }

    /**
     * Подменить DaData с кастомным callback-логикой.
     *
     * @param callable $callback (Request $request) => Response
     */
    protected function fakeDaDataUsing(callable $callback): void
    {
        Http::fake($callback);
    }
}
