<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\MenuType;
use Appsolutely\AIO\Tests\TestCase;

final class MenuTypeTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = MenuType::cases();

        $this->assertCount(5, $cases);
        $this->assertSame('link', MenuType::Link->value);
        $this->assertSame('dropdown', MenuType::Dropdown->value);
        $this->assertSame('divider', MenuType::Divider->value);
        $this->assertSame('label', MenuType::Label->value);
        $this->assertSame('custom', MenuType::Custom->value);
    }

    public function test_to_array_returns_label_string(): void
    {
        $this->assertSame('Link', MenuType::Link->toArray());
        $this->assertSame('Dropdown', MenuType::Dropdown->toArray());
        $this->assertSame('Custom', MenuType::Custom->toArray());
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(MenuType::Link, MenuType::from('link'));
        $this->assertSame(MenuType::Dropdown, MenuType::from('dropdown'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(MenuType::tryFrom('invalid'));
    }
}
