<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\MenuTarget;
use Appsolutely\AIO\Tests\TestCase;

final class MenuTargetTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = MenuTarget::cases();

        $this->assertCount(6, $cases);
        $this->assertSame('_self', MenuTarget::Self->value);
        $this->assertSame('_blank', MenuTarget::Blank->value);
        $this->assertSame('_parent', MenuTarget::Parent->value);
        $this->assertSame('_top', MenuTarget::Top->value);
        $this->assertSame('modal', MenuTarget::Modal->value);
        $this->assertSame('iframe', MenuTarget::Iframe->value);
    }

    public function test_to_array_returns_label_string(): void
    {
        $this->assertSame('Same Tab', MenuTarget::Self->toArray());
        $this->assertSame('New Tab', MenuTarget::Blank->toArray());
        $this->assertSame('Open in Modal', MenuTarget::Modal->toArray());
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(MenuTarget::Self, MenuTarget::from('_self'));
        $this->assertSame(MenuTarget::Blank, MenuTarget::from('_blank'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(MenuTarget::tryFrom('_invalid'));
    }
}
