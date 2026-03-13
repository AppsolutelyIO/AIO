<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\TranslatorType;
use Appsolutely\AIO\Tests\TestCase;

final class TranslatorTypeTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = TranslatorType::cases();

        $this->assertCount(4, $cases);
        $this->assertSame('Google', TranslatorType::Google->value);
        $this->assertSame('DeepSeek', TranslatorType::DeepSeek->value);
        $this->assertSame('OpenAI', TranslatorType::OpenAI->value);
        $this->assertSame('Manual', TranslatorType::Manual->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Google', TranslatorType::Google->label());
        $this->assertSame('DeepSeek', TranslatorType::DeepSeek->label());
        $this->assertSame('OpenAI', TranslatorType::OpenAI->label());
        $this->assertSame('Manual', TranslatorType::Manual->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = TranslatorType::toArray();

        $this->assertCount(4, $array);
        $this->assertSame('Google', $array['Google']);
        $this->assertSame('DeepSeek', $array['DeepSeek']);
        $this->assertSame('OpenAI', $array['OpenAI']);
        $this->assertSame('Manual', $array['Manual']);
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(TranslatorType::Google, TranslatorType::from('Google'));
        $this->assertSame(TranslatorType::Manual, TranslatorType::from('Manual'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(TranslatorType::tryFrom('invalid'));
    }
}
