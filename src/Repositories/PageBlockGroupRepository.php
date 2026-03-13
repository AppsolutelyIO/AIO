<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\PageBlockGroup;

final class PageBlockGroupRepository extends BaseRepository
{
    public function model(): string
    {
        return PageBlockGroup::class;
    }

    public function getCategorisedBlocks()
    {
        $query = $this->model->newQuery();

        $data =  $query
            ->whereHas('blocks', function ($query) {
                $query->status();
            })->with(['blocks' => function ($query) {
                $query->orderBy('sort')->status();
            }])->status()->orderBy('sort')->get();

        $data->each(function ($item) {
            $item->blocks = $item->blocks->map(function ($block) {
                $block->label   = $block->title;
                $block->type    = $block->reference;
                $block->content = $block->template;
                $block->tagName = 'section';

                return $block;
            });
        });

        return $data;
    }
}
