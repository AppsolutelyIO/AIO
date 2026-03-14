<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\CmsMenu;
use Appsolutely\AIO\Repositories\MenuRepository;
use Appsolutely\AIO\Services\Contracts\MenuServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

final readonly class MenuService implements MenuServiceInterface
{
    public function __construct(
        protected MenuRepository $menuRepository
    ) {}

    public function getActiveMenuTree(int $menuId, ?Carbon $datetime = null): Collection
    {
        return $this->menuRepository->getActiveMenuTree($menuId, $datetime);
    }

    public function getActiveMenus(int $menuId, ?Carbon $datetime = null): Collection
    {
        return $this->menuRepository->getActiveMenus($menuId, $datetime);
    }

    public function findByReference(string $reference): ?CmsMenu
    {
        return $this->menuRepository->findByReference($reference);
    }

    public function getMenusByReference(string $reference): \Illuminate\Support\Collection
    {
        $menu = $this->menuRepository->findByReference($reference);

        return $menu?->children ?? collect();
    }
}
