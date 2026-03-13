<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories\Traits;

use Appsolutely\AIO\Models\NestedSetModel;

trait ActiveTreeList
{
    // Need NestedSetModel and NodeTrait for the model

    /**
     * @return array|string[]
     */
    public function getActiveList(?int $parentId = null): array
    {
        $tree = $this->getTree($parentId);

        return NestedSetModel::formatTreeArray($tree);
    }

    public function getTree(?int $parentId = null, bool $all = false): mixed
    {
        $query = $this->model->newQuery();
        if (! empty($parentId)) {
            $query->where('parent_id', $parentId);
        }
        if (! $all) {
            $query->status();
        }

        return $query->get()->toTree();
    }
}
