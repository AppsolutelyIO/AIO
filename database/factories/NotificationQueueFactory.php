<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\NotificationQueueStatus;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\NotificationQueue;
use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\NotificationQueue>
 */
class NotificationQueueFactory extends Factory
{
    protected $model = NotificationQueue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rule_id'         => null,
            'template_id'     => NotificationTemplate::factory(),
            'form_entry_id'   => null, // Optional, can be set when needed
            'sender_id'       => null,
            'recipient_email' => fake()->safeEmail(),
            'subject'         => fake()->sentence(),
            'body_html'       => '<p>' . fake()->paragraph() . '</p>',
            'body_text'       => fake()->paragraph(),
            'trigger_data'    => [
                'form_name' => fake()->words(2, true),
                'user_name' => fake()->name(),
            ],
            'status'       => NotificationQueueStatus::Pending,
            'scheduled_at' => now(),
            'attempts'     => 0,
        ];
    }

    /**
     * Indicate that the notification is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'   => NotificationQueueStatus::Pending,
            'sent_at'  => null,
            'attempts' => 0,
        ]);
    }

    /**
     * Indicate that the notification has been sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => NotificationQueueStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    /**
     * Indicate that the notification has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => NotificationQueueStatus::Failed,
            'attempts'      => 3,
            'error_message' => fake()->sentence(),
        ]);
    }

    /**
     * Associate with a form entry.
     */
    public function forEntry(FormEntry|int $entry): static
    {
        $entryId = $entry instanceof FormEntry ? $entry->id : $entry;

        return $this->state(fn (array $attributes) => [
            'form_entry_id' => $entryId,
        ]);
    }

    /**
     * Associate with a notification rule.
     */
    public function forRule(NotificationRule|int $rule): static
    {
        $ruleId = $rule instanceof NotificationRule ? $rule->id : $rule;

        return $this->state(fn (array $attributes) => [
            'rule_id' => $ruleId,
        ]);
    }

    /**
     * Set scheduled time in the future.
     */
    public function scheduledLater(int $minutes = 60): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Set template ID.
     */
    public function withTemplate(int $templateId): static
    {
        return $this->state(fn (array $attributes) => [
            'template_id' => $templateId,
        ]);
    }
}
