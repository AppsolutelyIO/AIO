<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Models\NotificationSender;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class NotificationSenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_active_returns_only_active_senders(): void
    {
        $active   = NotificationSender::factory()->create(['is_active' => true]);
        $inactive = NotificationSender::factory()->create(['is_active' => false]);

        $result = NotificationSender::query()->active()->get();

        $this->assertTrue($result->contains('id', $active->id));
        $this->assertFalse($result->contains('id', $inactive->id));
    }

    public function test_scope_category_filters_by_category(): void
    {
        $internal = NotificationSender::factory()->create(['category' => 'internal']);
        $external = NotificationSender::factory()->create(['category' => 'external']);

        $result = NotificationSender::query()->category('internal')->get();

        $this->assertCount(1, $result);
        $this->assertEquals($internal->id, $result->first()->id);
    }

    public function test_scope_default_returns_default_senders(): void
    {
        $default    = NotificationSender::factory()->create(['is_default' => true, 'category' => 'external']);
        $nonDefault = NotificationSender::factory()->create(['is_default' => false, 'category' => 'external']);

        $result = NotificationSender::query()->default()->get();

        $this->assertTrue($result->contains('id', $default->id));
        $this->assertFalse($result->contains('id', $nonDefault->id));
    }

    public function test_scope_default_filters_by_category(): void
    {
        $defaultExternal = NotificationSender::factory()->create(['is_default' => true, 'category' => 'external']);
        $defaultInternal = NotificationSender::factory()->create(['is_default' => true, 'category' => 'internal']);

        $result = NotificationSender::query()->default('external')->get();

        $this->assertCount(1, $result);
        $this->assertEquals($defaultExternal->id, $result->first()->id);
    }

    public function test_smtp_password_is_encrypted_on_set(): void
    {
        $sender = NotificationSender::factory()->create(['smtp_password' => 'secret123']);

        $rawValue = $sender->getAttributes()['smtp_password'];

        $this->assertNotEquals('secret123', $rawValue);
        $this->assertEquals('secret123', decrypt($rawValue));
    }

    public function test_decrypted_password_returns_null_when_no_password(): void
    {
        $sender = NotificationSender::factory()->create(['smtp_password' => null]);

        $this->assertNull($sender->decrypted_password);
    }

    public function test_decrypted_password_returns_decrypted_value(): void
    {
        $sender = NotificationSender::factory()->create(['smtp_password' => 'secret123']);

        $this->assertEquals('secret123', $sender->decrypted_password);
    }

    public function test_service_config_is_encrypted_on_set(): void
    {
        $sender                 = NotificationSender::factory()->create();
        $sender->service_config = ['api_key' => 'test123'];
        $sender->save();
        $sender->refresh();

        $rawValue = $sender->getAttributes()['service_config'];
        $this->assertNotEquals(json_encode(['api_key' => 'test123']), $rawValue);
    }

    public function test_decrypted_service_config_returns_null_when_empty(): void
    {
        $sender = NotificationSender::factory()->create();

        $this->assertNull($sender->decrypted_service_config);
    }

    public function test_decrypted_service_config_returns_array(): void
    {
        $sender                 = NotificationSender::factory()->create();
        $sender->service_config = ['api_key' => 'test123', 'domain' => 'example.com'];
        $sender->save();
        $sender->refresh();

        $config = $sender->decrypted_service_config;

        $this->assertIsArray($config);
        $this->assertEquals('test123', $config['api_key']);
        $this->assertEquals('example.com', $config['domain']);
    }

    public function test_is_internal_returns_true_for_internal_category(): void
    {
        $sender = NotificationSender::factory()->create(['category' => 'internal']);

        $this->assertTrue($sender->isInternal());
        $this->assertFalse($sender->isExternal());
        $this->assertFalse($sender->isSystem());
    }

    public function test_is_external_returns_true_for_external_category(): void
    {
        $sender = NotificationSender::factory()->create(['category' => 'external']);

        $this->assertFalse($sender->isInternal());
        $this->assertTrue($sender->isExternal());
        $this->assertFalse($sender->isSystem());
    }

    public function test_is_system_returns_true_for_system_category(): void
    {
        $sender = NotificationSender::factory()->create(['category' => 'system']);

        $this->assertFalse($sender->isInternal());
        $this->assertFalse($sender->isExternal());
        $this->assertTrue($sender->isSystem());
    }
}
