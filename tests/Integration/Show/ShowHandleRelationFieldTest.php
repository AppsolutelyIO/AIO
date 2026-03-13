<?php

namespace Appsolutely\AIO\Tests\Integration\Show;

use Appsolutely\AIO\Show;
use Appsolutely\AIO\Tests\Integration\TestCase;

class ShowHandleRelationFieldTest extends TestCase
{
    // --- Show::handleRelationField() ---
    // This is a protected method, so we use reflection to test it

    private function callHandleRelationField(Show $show, string $method, array $arguments)
    {
        $ref = new \ReflectionMethod($show, 'handleRelationField');
        $ref->setAccessible(true);

        return $ref->invoke($show, $method, $arguments);
    }

    public function test_handle_relation_field_returns_false_with_no_arguments()
    {
        $show = new Show(new \Illuminate\Support\Fluent(['id' => 1]));
        $result = $this->callHandleRelationField($show, 'relation', []);
        $this->assertFalse($result);
    }

    public function test_handle_relation_field_returns_false_with_non_closure_single_argument()
    {
        $show = new Show(new \Illuminate\Support\Fluent(['id' => 1]));
        $result = $this->callHandleRelationField($show, 'relation', ['string']);
        $this->assertFalse($result);
    }

    public function test_handle_relation_field_returns_false_with_two_non_closure_arguments()
    {
        $show = new Show(new \Illuminate\Support\Fluent(['id' => 1]));
        $result = $this->callHandleRelationField($show, 'relation', ['label', 'not_closure']);
        $this->assertFalse($result);
    }

    public function test_handle_relation_field_returns_false_with_three_arguments()
    {
        $show = new Show(new \Illuminate\Support\Fluent(['id' => 1]));
        $result = $this->callHandleRelationField($show, 'relation', ['a', 'b', 'c']);
        $this->assertFalse($result);
    }
}
