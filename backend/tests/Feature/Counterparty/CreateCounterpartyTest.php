<?php

declare(strict_types=1);

namespace Tests\Feature\Counterparty;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;
use Tests\Traits\FakesDaData;

class CreateCounterpartyTest extends FeatureTestCase
{
    use FakesDaData;

    #[Test]
    public function create_counterparty_success(): void
    {
        $this->fakeDaDataSuccessForInn('7707083893', [
            'name' => 'ООО Ромашка',
            'ogrn' => '1234567890123',
            'address' => 'г. Москва, ул. Ленина, 1',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/counterparties', [
            'inn' => '7707083893',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'inn', 'name', 'ogrn', 'address', 'created_at']);

        $this->assertDatabaseHas('counterparties', [
            'inn' => '7707083893',
            'name' => 'ООО Ромашка',
        ]);
    }

    #[Test]
    public function create_counterparty_not_found_returns_404_and_does_not_create(): void
    {
        $this->fakeDaDataNotFound();

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/counterparties', [
            'inn' => '0000000000',
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => __('dialogue.external_api.not_found')]);

        $this->assertDatabaseMissing('counterparties', ['inn' => '0000000000']);
    }

    #[Test]
    public function create_counterparty_external_service_error_returns_502(): void
    {
        $this->fakeDaDataError(500);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/counterparties', [
            'inn' => '7707083893',
        ]);

        $response->assertStatus(502)
            ->assertJson(['message' => __('dialogue.external_api.unavailable')]);

        $this->assertDatabaseMissing('counterparties', ['inn' => '7707083893']);
    }

    #[Test]
    public function create_counterparty_unauthenticated_returns_401(): void
    {
        // Без actingAs
        $response = $this->postJson('/api/v1/counterparties', [
            'inn' => '7707083893',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }
}
