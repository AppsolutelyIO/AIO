<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\PageType;
use Appsolutely\AIO\Tests\TestCase;

final class PageTypeTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = PageType::cases();

        $this->assertCount(2, $cases);
        $this->assertSame('nested', PageType::Nested->value);
        $this->assertSame('root', PageType::Root->value);
    }

    public function test_to_array_returns_label_string(): void
    {
        $this->assertSame('Nested', PageType::Nested->toArray());
        $this->assertSame('Root', PageType::Root->toArray());
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(PageType::Nested, PageType::from('nested'));
        $this->assertSame(PageType::Root, PageType::from('root'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(PageType::tryFrom('invalid'));
    }
}
