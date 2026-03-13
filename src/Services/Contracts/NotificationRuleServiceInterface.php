<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Model;
use Appsolutely\AIO\Models\NotificationRule;
use Illuminate\Database\Eloquent\Collection;

interface NotificationRuleServiceInterface
{
    /**
     * Find rules for specific trigger
     */
    public function findRulesForTrigger(string $triggerType, string $reference): Collection;

    /**
     * Evaluate if rule conditions are met
     */
    public function evaluateConditions(NotificationRule $rule, array $data): bool;

    /**
     * Get recipients for a rule
     */
    public function getRecipients(NotificationRule $rule, array $data): array;

    /**
     * Create new notification rule
     */
    public function createRule(array $data): Model;

    /**
     * Update existing rule
     */
    public function updateRule(NotificationRule $rule, array $data): NotificationRule;

    /**
     * Test rule with sample data
     */
    public function testRule(NotificationRule $rule, array $sampleData): array;

    /**
     * Test rule by ID with sample data
     */
    public function testRuleById(int $id): array;

    /**
     * Get available trigger types
     */
    public function getAvailableTriggerTypes(): array;

    /**
     * Get available condition operators
     */
    public function getConditionOperators(): array;

    /**
     * Get recipient type options
     */
    public function getRecipientTypes(): array;

    /**
     * Validate rule data
     */
    public function validateRule(array $data): array;
}
