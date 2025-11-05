<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\TestUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Базовый класс для feature-тестов: накатывает миграции, сеет пользователя и задаёт базовый конфиг.
 */
abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed тестового пользователя (TestUserSeeder должен создавать пользователя test@example.com)
        $this->seed(TestUserSeeder::class);

        // Базовые конфиги (перекрываются при необходимости в конкретных тестах)
        config([
            'services.dadata.token' => 'test-token',
            'services.dadata.base_url' => 'https://suggestions.dadata.ru/suggestions/api/4_1/rs',
            'services.dadata.cache_ttl' => 60,
        ]);
    }
}
