<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\ReleaseChannel;
use Appsolutely\AIO\Tests\TestCase;

final class ReleaseChannelTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = ReleaseChannel::cases();

        $this->assertCount(3, $cases);
        $this->assertSame('stable', ReleaseChannel::Stable->value);
        $this->assertSame('beta', ReleaseChannel::Beta->value);
        $this->assertSame('dev', ReleaseChannel::Dev->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Stable', ReleaseChannel::Stable->label());
        $this->assertSame('Beta', ReleaseChannel::Beta->label());
        $this->assertSame('Dev', ReleaseChannel::Dev->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = ReleaseChannel::toArray();

        $this->assertCount(3, $array);
        $this->assertSame('Stable', $array['stable']);
        $this->assertSame('Beta', $array['beta']);
        $this->assertSame('Dev', $array['dev']);
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(ReleaseChannel::Stable, ReleaseChannel::from('stable'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(ReleaseChannel::tryFrom('nightly'));
    }
}
