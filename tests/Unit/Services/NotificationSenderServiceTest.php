<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Models\NotificationSender;
use Appsolutely\AIO\Services\NotificationSenderService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class NotificationSenderServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationSenderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationSenderService::class);
    }

    private function createSender(array $attrs = []): NotificationSender
    {
        return NotificationSender::create(array_merge([
            'name'         => 'Test Sender',
            'slug'         => 'test-sender-' . uniqid(),
            'type'         => 'smtp',
            'from_address' => 'from@example.com',
            'category'     => 'internal',
            'is_default'   => false,
            'is_active'    => true,
            'priority'     => 0,
        ], $attrs));
    }

    // --- getSenderForRule ---

    public function test_get_sender_for_rule_returns_null_when_no_sender_configured(): void
    {
        $rule = NotificationRule::factory()->create([
            'sender_id'      => null,
            'recipient_type' => 'admin',
        ]);

        $result = $this->service->getSenderForRule($rule);

        $this->assertNull($result);
    }

    public function test_get_sender_for_rule_returns_sender_when_explicitly_set_and_active(): void
    {
        $sender = $this->createSender(['is_active' => true]);
        $rule   = NotificationRule::factory()->create([
            'sender_id'      => $sender->id,
            'recipient_type' => 'admin',
        ]);

        $result = $this->service->getSenderForRule($rule);

        $this->assertInstanceOf(NotificationSender::class, $result);
        $this->assertEquals($sender->id, $result->id);
    }

    public function test_get_sender_for_rule_falls_back_when_sender_is_inactive(): void
    {
        $sender = $this->createSender(['is_active' => false]);
        $rule   = NotificationRule::factory()->create([
            'sender_id'      => $sender->id,
            'recipient_type' => 'user',
        ]);

        // No default sender configured, so result should be null
        $result = $this->service->getSenderForRule($rule);

        $this->assertNull($result);
    }

    // --- getFromAddress ---

    public function test_get_from_address_returns_array_with_address_and_name(): void
    {
        $sender = $this->createSender([
            'from_address' => 'no-reply@example.com',
            'from_name'    => 'Example System',
        ]);

        $result = $this->service->getFromAddress($sender);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('address', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('no-reply@example.com', $result['address']);
        $this->assertEquals('Example System', $result['name']);
    }

    public function test_get_from_address_uses_app_name_when_from_name_is_null(): void
    {
        $sender = $this->createSender([
            'from_address' => 'test@example.com',
            'from_name'    => null,
        ]);

        $result = $this->service->getFromAddress($sender);

        $this->assertEquals(config('app.name'), $result['name']);
    }

    // --- configureMailer ---

    public function test_configure_mailer_sets_config_for_smtp_sender(): void
    {
        $sender = $this->createSender([
            'type'          => 'smtp',
            'smtp_host'     => 'smtp.example.com',
            'smtp_port'     => 587,
            'smtp_username' => 'user@example.com',
        ]);

        $this->service->configureMailer($sender);

        $mailerName = "sender_{$sender->id}";
        $this->assertEquals('smtp', config("mail.mailers.{$mailerName}.transport"));
        $this->assertEquals('smtp.example.com', config("mail.mailers.{$mailerName}.host"));
    }
}
