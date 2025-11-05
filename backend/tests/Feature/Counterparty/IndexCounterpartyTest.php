<?php

declare(strict_types=1);

namespace Tests\Feature\Counterparty;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;
use Tests\Traits\FakesDaData;

class IndexCounterpartyTest extends FeatureTestCase
{
    use FakesDaData;

    #[Test]
    public function index_returns_list_and_meta_pagination(): void
    {
        // Подмена DaData: возвращаем разные ответы в зависимости от поля 'query' в теле запроса
        $this->fakeDaDataUsing(function ($request) {
            $body = json_decode($request->body(), true) ?: [];
            $query = $body['query'] ?? null;

            if ($query === '7707083893') {
                $payload = [
                    'suggestions' => [
                        [
                            'value' => 'ООО Ромашка',
                            'data' => [
                                'name' => ['short_with_opf' => 'ООО Ромашка'],
                                'ogrn' => '111',
                                'address' => ['unrestricted_value' => 'addr1'],
                            ],
                        ],
                    ],
                ];

                return Http::response($payload, 200, ['Content-Type' => 'application/json']);
            }

            // default / второй inn
            $payload = [
                'suggestions' => [
                    [
                        'value' => 'ООО Берёзка',
                        'data' => [
                            'name' => ['short_with_opf' => 'ООО Берёзка'],
                            'ogrn' => '222',
                            'address' => ['unrestricted_value' => 'addr2'],
                        ],
                    ],
                ],
            ];

            return Http::response($payload, 200, ['Content-Type' => 'application/json']);
        });

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user, 'sanctum');

        // создаём 2 контрагента — сервис отправляет POST к DaData и сохраняет записи
        $this->postJson('/api/v1/counterparties', ['inn' => '7707083893'])->assertStatus(201);
        $this->postJson('/api/v1/counterparties', ['inn' => '5000000000'])->assertStatus(201);

        // Получаем список
        $response = $this->getJson('/api/v1/counterparties?per_page=15');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    ['id', 'inn', 'name', 'ogrn', 'address', 'created_at'],
                ],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $this->assertCount(2, $response->json('data'));
    }
}
