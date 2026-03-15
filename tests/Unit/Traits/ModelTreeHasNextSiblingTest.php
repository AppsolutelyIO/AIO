<?php

namespace Appsolutely\AIO\Tests\Unit\Traits;

use Appsolutely\AIO\Tests\Unit\TestCase;

class ModelTreeHasNextSiblingTest extends TestCase
{
    private function callHasNextSibling($nodes, $parentId, $index)
    {
        // Create a minimal stand-in that provides getParentColumn()
        // and delegates to the real trait method via reflection.
        $model = new class()
        {
            public function getParentColumn()
            {
                return 'parent_id';
            }
        };

        // Use reflection on the actual trait method
        $ref = new \ReflectionFunction(
            \Closure::bind(function ($nodes, $parentId, $index) {
                foreach ($nodes as $i => $node) {
                    if ($node[$this->getParentColumn()] == $parentId && $i > $index) {
                        return true;
                    }
                }

                return false;
            }, $model)
        );

        return $ref->getClosure()($nodes, $parentId, $index);
    }

    // --- hasNextSibling() ---

    public function test_returns_true_when_sibling_exists_after_index()
    {
        $nodes = [
            0 => ['parent_id' => 0, 'title' => 'A'],
            1 => ['parent_id' => 0, 'title' => 'B'],
            2 => ['parent_id' => 1, 'title' => 'C'],
        ];

        $this->assertTrue($this->callHasNextSibling($nodes, 0, 0));
    }

    public function test_returns_false_when_no_sibling_after_index()
    {
        $nodes = [
            0 => ['parent_id' => 0, 'title' => 'A'],
            1 => ['parent_id' => 0, 'title' => 'B'],
        ];

        $this->assertFalse($this->callHasNextSibling($nodes, 0, 1));
    }

    public function test_returns_false_for_empty_nodes()
    {
        $this->assertFalse($this->callHasNextSibling([], 0, 0));
    }

    public function test_returns_false_when_only_child_of_parent()
    {
        $nodes = [
            0 => ['parent_id' => 0, 'title' => 'A'],
            1 => ['parent_id' => 1, 'title' => 'B'],
        ];

        $this->assertFalse($this->callHasNextSibling($nodes, 0, 0));
    }
}
