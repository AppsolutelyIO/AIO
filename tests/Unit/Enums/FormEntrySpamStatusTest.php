<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\FormEntrySpamStatus;
use Appsolutely\AIO\Tests\TestCase;

final class FormEntrySpamStatusTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = FormEntrySpamStatus::cases();

        $this->assertCount(2, $cases);
        $this->assertSame(0, FormEntrySpamStatus::Valid->value);
        $this->assertSame(1, FormEntrySpamStatus::Spam->value);
    }

    public function test_label_returns_string(): void
    {
        $this->assertSame('Valid', FormEntrySpamStatus::Valid->label());
        $this->assertSame('Spam', FormEntrySpamStatus::Spam->label());
    }

    // --- isSpam ---

    public function test_valid_is_not_spam(): void
    {
        $this->assertFalse(FormEntrySpamStatus::Valid->isSpam());
    }

    public function test_spam_is_spam(): void
    {
        $this->assertTrue(FormEntrySpamStatus::Spam->isSpam());
    }

    // --- toYesNo ---

    public function test_valid_to_yes_no_returns_no(): void
    {
        $this->assertSame('No', FormEntrySpamStatus::Valid->toYesNo());
    }

    public function test_spam_to_yes_no_returns_yes(): void
    {
        $this->assertSame('Yes', FormEntrySpamStatus::Spam->toYesNo());
    }

    // --- toYesNoFrom ---

    public function test_to_yes_no_from_enum_instance(): void
    {
        $this->assertSame('Yes', FormEntrySpamStatus::toYesNoFrom(FormEntrySpamStatus::Spam));
        $this->assertSame('No', FormEntrySpamStatus::toYesNoFrom(FormEntrySpamStatus::Valid));
    }

    public function test_to_yes_no_from_truthy_int(): void
    {
        $this->assertSame('Yes', FormEntrySpamStatus::toYesNoFrom(1));
    }

    public function test_to_yes_no_from_falsy_int(): void
    {
        $this->assertSame('No', FormEntrySpamStatus::toYesNoFrom(0));
    }

    public function test_to_yes_no_from_truthy_bool(): void
    {
        $this->assertSame('Yes', FormEntrySpamStatus::toYesNoFrom(true));
    }

    public function test_to_yes_no_from_falsy_bool(): void
    {
        $this->assertSame('No', FormEntrySpamStatus::toYesNoFrom(false));
    }

    public function test_to_yes_no_from_null(): void
    {
        $this->assertSame('No', FormEntrySpamStatus::toYesNoFrom(null));
    }

    // --- toArray ---

    public function test_to_array_returns_keyed_labels(): void
    {
        $array = FormEntrySpamStatus::toArray();

        $this->assertCount(2, $array);
        $this->assertArrayHasKey(0, $array);
        $this->assertArrayHasKey(1, $array);
        $this->assertSame('Valid', $array[0]);
        $this->assertSame('Spam', $array[1]);
    }
}
