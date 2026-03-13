<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Kalnoy\Nestedset\Collection;
use Kalnoy\Nestedset\NodeTrait;

class NestedSetModel extends Model
{
    use NodeTrait;

    protected $defaultParentId = null;

    protected string $orderColumn = 'left';

    public function getLftName(): string
    {
        return 'left';
    }

    public function getRgtName(): string
    {
        return 'right';
    }

    public static function formatTreeArray(Collection $tree, $prefix = '|-- ')
    {
        $data     = [];
        $traverse = function ($categories, $prefix = '') use (&$traverse, &$data) {
            foreach ($categories as $category) {
                $item = [(string) $category->id => $prefix . ' ' . $category->title];
                $data = $data + $item;
                $traverse($category->children, str_repeat('&nbsp;', 6) . $prefix);
            }
        };

        $traverse($tree, $prefix);

        return $data;
    }

    public static function restoring($callback)
    {
        static::registerModelEvent('restoring', $callback);
    }

    public static function restored($callback)
    {
        static::registerModelEvent('restored', $callback);
    }
}
