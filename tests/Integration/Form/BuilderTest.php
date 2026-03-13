<?php

namespace Appsolutely\AIO\Tests\Integration\Form;

use Appsolutely\AIO\Form;
use Appsolutely\AIO\Form\Builder;
use Appsolutely\AIO\Tests\Integration\TestCase;

class BuilderTest extends TestCase
{
    protected function createBuilder(): Builder
    {
        return new Builder(new Form());
    }

    // --- hasWrapper ---

    public function test_has_wrapper_returns_false_by_default()
    {
        $builder = $this->createBuilder();
        $this->assertFalse($builder->hasWrapper());
    }

    public function test_has_wrapper_returns_true_after_wrap()
    {
        $builder = $this->createBuilder();
        $builder->wrap(function () {});
        $this->assertTrue($builder->hasWrapper());
    }

    public function test_has_wrapper_returns_bool_type()
    {
        $builder = $this->createBuilder();
        $this->assertIsBool($builder->hasWrapper());

        $builder->wrap(function () {});
        $this->assertIsBool($builder->hasWrapper());
    }
}
