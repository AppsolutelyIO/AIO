<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\NotificationRule>
 */
class NotificationRuleFactory extends Factory
{
    protected $model = NotificationRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => fake()->words(3, true),
            'trigger_type'      => 'form_submission',
            'trigger_reference' => fake()->slug(),
            'template_id'       => NotificationTemplate::factory(),
            'sender_id'         => null,
            'recipient_type'    => 'admin',
            'recipient_emails'  => json_encode([fake()->safeEmail()]),
            'conditions'        => null,
            'delay_minutes'     => 0,
            'status'            => Status::ACTIVE,
        ];
    }

    /**
     * Indicate that the rule is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::ACTIVE,
        ]);
    }

    /**
     * Indicate that the rule is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::INACTIVE,
        ]);
    }

    /**
     * Set the trigger type to form submission.
     */
    public function forFormSubmission(?string $formSlug = null): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type'      => 'form_submission',
            'trigger_reference' => $formSlug ?? fake()->slug(),
        ]);
    }

    /**
     * Set the trigger to wildcard (all forms).
     */
    public function forAllForms(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type'      => 'form_submission',
            'trigger_reference' => '*',
        ]);
    }

    /**
     * Set the trigger type to user registration.
     */
    public function forUserRegistration(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type'      => 'user_registration',
            'trigger_reference' => null,
        ]);
    }

    /**
     * Schedule notification to be sent later.
     */
    public function delayed(int $minutes = 60): static
    {
        return $this->state(fn (array $attributes) => [
            'delay_minutes' => $minutes,
        ]);
    }

    /**
     * Set immediate delivery.
     */
    public function immediate(): static
    {
        return $this->state(fn (array $attributes) => [
            'delay_minutes' => 0,
        ]);
    }

    /**
     * Set recipient emails.
     */
    public function toEmails(array $emails): static
    {
        return $this->state(fn (array $attributes) => [
            'recipient_emails' => json_encode($emails),
        ]);
    }

    /**
     * Add conditions to the rule.
     */
    public function withConditions(array $conditions): static
    {
        return $this->state(fn (array $attributes) => [
            'conditions' => json_encode($conditions),
        ]);
    }
}
