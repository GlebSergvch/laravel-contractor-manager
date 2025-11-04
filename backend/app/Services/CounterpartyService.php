<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Counterparty\CreateCounterpartyDto;
use App\Exceptions\ExternalApiException;
use App\Models\Counterparty;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CounterpartyService
{
    /**
     * TTL for caching DaData responses (seconds).
     *
     * @var int
     */
    private int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = (int) config('services.dadata.cache_ttl', 3600 * 24);
    }

    /**
     * Создать контрагента по ИНН, получив данные из DaData.
     *
     * @param User $user
     * @param CreateCounterpartyDto $dto
     * @return Counterparty
     *
     * @throws ExternalApiException
     */
    public function createFromInn(User $user, CreateCounterpartyDto $dto): Counterparty
    {
        $existing = Counterparty::where('user_id', $user->id)
            ->where('inn', $dto->inn)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $token = config('services.dadata.token');
        $base = rtrim(config('services.dadata.base_url', ''), '/');

        if (empty($token) || empty($base)) {
            Log::error('DaData configuration missing', [
                'has_token' => ! empty($token),
                'base_config' => $base !== '',
            ]);

            throw new ExternalApiException('dialogue.external_api.unavailable', 502);
        }

        $cacheKey = "dadata:inn:{$dto->inn}";
        $parsed = Cache::get($cacheKey);
        if (! is_null($parsed)) {
            return $this->persistCounterparty($user->id, $dto->inn, $parsed);
        }

        $url = "{$base}/findById/party";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Token {$token}",
                'Accept' => 'application/json',
            ])
                ->timeout(5)
                ->retry(2, 150)
                ->post($url, [
                    'query' => $dto->inn,
                    'count' => 1,
                ]);
        } catch (\Throwable $e) {
            Log::error('DaData network error', [
                'inn' => $dto->inn,
                'error' => $e->getMessage(),
            ]);

            throw new ExternalApiException('dialogue.external_api.unavailable', 502);
        }

        if ($response->status() === 429) {
            Log::warning('DaData rate limit', ['inn' => $dto->inn]);
            throw new ExternalApiException('dialogue.external_api.rate_limit', 429);
        }

        try {
            $response->throw();
        } catch (RequestException $e) {
            $body = (string) $e->response?->body();
            Log::error('DaData returned error', [
                'status' => $e->response?->status(),
                'body_preview' => Str::limit($body, 1000),
                'inn' => $dto->inn,
            ]);

            throw new ExternalApiException('dialogue.external_api.returned_error', 502);
        }

        $json = $response->json();
        $suggestions = $json['suggestions'] ?? [];

        if (empty($suggestions)) {
            Cache::put($cacheKey, ['not_found' => true], 60 * 5);
            throw new ExternalApiException('dialogue.external_api.not_found', 404);
        }

        $data = $suggestions[0]['data'] ?? [];
        $name = $data['name']['short_with_opf'] ?? $data['name']['short'] ?? $data['name']['full_with_opf'] ?? null;
        $ogrn = $data['ogrn'] ?? null;
        $address = $data['address']['unrestricted_value'] ?? null;

        if (empty($name)) {
            Log::warning('DaData returned incomplete data', [
                'inn' => $dto->inn,
                'response_preview' => Str::limit(json_encode($json), 1000),
            ]);

            throw new ExternalApiException('dialogue.external_api.incomplete_data', 502);
        }

        $parsed = [
            'name' => $name,
            'ogrn' => $ogrn,
            'address' => $address,
            'raw' => $json,
        ];

        Cache::put($cacheKey, $parsed, $this->cacheTtl);

        return $this->persistCounterparty($user->id, $dto->inn, $parsed);
    }

    /**
     * Записать контрагента в БД (в отдельный метод для тестируемости и повторного использования).
     *
     * @param int $userId
     * @param string $inn
     * @param array $parsed
     * @return Counterparty
     */
    private function persistCounterparty(int $userId, string $inn, array $parsed): Counterparty
    {
        $existing = Counterparty::where('user_id', $userId)
            ->where('inn', $inn)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $cp = Counterparty::create([
            'user_id' => $userId,
            'inn' => $inn,
            'name' => (string) ($parsed['name'] ?? $parsed['raw']['suggestions'][0]['value'] ?? $inn),
            'ogrn' => $parsed['ogrn'] ?? null,
            'address' => $parsed['address'] ?? null,
            'raw_response' => $parsed['raw'] ?? null,
        ]);

        Log::info('Counterparty saved', [
            'user_id' => $userId,
            'inn' => $inn,
            'counterparty_id' => $cp->id,
        ]);

        return $cp;
    }

    /**
     * Получить список контрагентов для пользователя (пагинация).
     *
     * @param User $user
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listForUser(User $user, int $perPage = 15)
    {
        return Counterparty::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
