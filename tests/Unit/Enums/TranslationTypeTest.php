<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\TranslationType;
use Appsolutely\AIO\Tests\TestCase;

final class TranslationTypeTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = TranslationType::cases();

        $this->assertCount(3, $cases);
        $this->assertSame('php', TranslationType::Php->value);
        $this->assertSame('blade', TranslationType::Blade->value);
        $this->assertSame('variable', TranslationType::Variable->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('PHP', TranslationType::Php->label());
        $this->assertSame('Blade', TranslationType::Blade->label());
        $this->assertSame('Variable', TranslationType::Variable->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = TranslationType::toArray();

        $this->assertCount(3, $array);
        $this->assertSame('PHP', $array['php']);
        $this->assertSame('Blade', $array['blade']);
        $this->assertSame('Variable', $array['variable']);
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(TranslationType::Php, TranslationType::from('php'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(TranslationType::tryFrom('invalid'));
    }
}
