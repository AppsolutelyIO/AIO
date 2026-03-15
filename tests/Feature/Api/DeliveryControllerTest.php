<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature\Api;

use App\Models\User;
use Appsolutely\AIO\Models\DeliveryToken;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_delivery_token_status(): void
    {
        $user  = User::factory()->create();
        $token = DeliveryToken::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.deliveries.show', $token->token));

        $response->assertOk()
            ->assertJsonPath('data.token', $token->token)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_show_returns_404_for_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.deliveries.show', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_show_requires_authentication(): void
    {
        $token = DeliveryToken::factory()->create();

        $response = $this->getJson(route('api.deliveries.show', $token->token));

        $response->assertUnauthorized();
    }

    public function test_fulfill_delivery(): void
    {
        $user  = User::factory()->create();
        $token = DeliveryToken::factory()->create([
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.deliveries.fulfill'), [
                'token'            => $token->token,
                'delivery_payload' => 'License: ABCD-1234',
                'channel'          => 'api',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'delivered')
            ->assertJsonPath('message', 'Delivery fulfilled successfully.');
    }

    public function test_fulfill_requires_authentication(): void
    {
        $token = DeliveryToken::factory()->create();

        $response = $this->postJson(route('api.deliveries.fulfill'), [
            'token'            => $token->token,
            'delivery_payload' => 'payload',
        ]);

        $response->assertUnauthorized();
    }

    public function test_fulfill_validates_token_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.deliveries.fulfill'), [
                'delivery_payload' => 'payload',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('token');
    }

    public function test_fulfill_validates_payload_required(): void
    {
        $user  = User::factory()->create();
        $token = DeliveryToken::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.deliveries.fulfill'), [
                'token' => $token->token,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('delivery_payload');
    }

    public function test_fulfill_returns_error_for_already_delivered(): void
    {
        $user  = User::factory()->create();
        $token = DeliveryToken::factory()->delivered()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.deliveries.fulfill'), [
                'token'            => $token->token,
                'delivery_payload' => 'payload',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', false);
    }

    public function test_fulfill_returns_404_for_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.deliveries.fulfill'), [
                'token'            => str_repeat('a', 64),
                'delivery_payload' => 'payload',
            ]);

        $response->assertNotFound();
    }
}
