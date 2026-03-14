<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\CmsMenu;
use Appsolutely\AIO\Repositories\Traits\ActiveTreeList;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

final class MenuRepository extends BaseRepository
{
    use ActiveTreeList;

    public function model(): string
    {
        return CmsMenu::class;
    }

    public function getActiveMenus(int $menuId, ?Carbon $datetime): Collection
    {
        return $this->model->status()
            ->published($datetime)
            ->where('parent_id', $menuId)
            ->orderBy('left')
            ->get();
    }

    public function getActiveMenuTree(int $menuId, ?Carbon $datetime): \Kalnoy\Nestedset\Collection
    {
        /** @var \Kalnoy\Nestedset\Collection $activeMenus */
        $activeMenus = $this->getActiveMenus($menuId, $datetime);

        return $activeMenus->toTree();
    }

    /**
     * Find active menus that reference a given page URL/slug.
     */
    public function findActiveMenusByPageSlug(string $slug): Collection
    {
        $cleanSlug = trim($slug, '/');

        if ($cleanSlug === '') {
            return new Collection();
        }

        return $this->model->newQuery()
            ->where('status', Status::ACTIVE->value)
            ->where(function ($query) use ($cleanSlug) {
                $query->where('url', $cleanSlug)
                    ->orWhere('url', '/' . $cleanSlug);
            })
            ->get();
    }

    public function findByReference(string $reference, bool $status = true): ?CmsMenu
    {
        return $this->model
            ->with([
                'children' => function ($query) use ($status) {
                    $query->where('status', $status);
                },
            ])
            ->where('reference', $reference)
            ->first();
    }
}
