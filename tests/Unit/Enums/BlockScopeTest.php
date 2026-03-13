<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\BlockScope;
use Appsolutely\AIO\Tests\TestCase;

final class BlockScopeTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = BlockScope::cases();

        $this->assertCount(2, $cases);
        $this->assertSame('page', BlockScope::Page->value);
        $this->assertSame('global', BlockScope::Global->value);
    }

    public function test_to_array_returns_label_string(): void
    {
        $this->assertSame('Page', BlockScope::Page->toArray());
        $this->assertSame('Global', BlockScope::Global->toArray());
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(BlockScope::Page, BlockScope::from('page'));
        $this->assertSame(BlockScope::Global, BlockScope::from('global'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(BlockScope::tryFrom('invalid'));
    }
}
