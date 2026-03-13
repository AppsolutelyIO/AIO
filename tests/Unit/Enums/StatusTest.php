<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Tests\TestCase;

final class StatusTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = Status::cases();

        $this->assertCount(2, $cases);
        $this->assertSame(0, Status::INACTIVE->value);
        $this->assertSame(1, Status::ACTIVE->value);
    }

    public function test_to_array_returns_keyed_labels(): void
    {
        $array = Status::toArray();

        $this->assertCount(2, $array);
        $this->assertSame('Inactive', $array[0]);
        $this->assertSame('Active', $array[1]);
    }

    public function test_to_translated_array_returns_translated_labels(): void
    {
        $array = Status::toTranslatedArray();

        $this->assertCount(2, $array);
        $this->assertIsString($array[0]);
        $this->assertIsString($array[1]);
    }

    public function test_can_construct_from_int_value(): void
    {
        $this->assertSame(Status::ACTIVE, Status::from(1));
        $this->assertSame(Status::INACTIVE, Status::from(0));
    }
}
