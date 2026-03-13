<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Menu;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface MenuServiceInterface
{
    /**
     * Get active menu tree
     */
    public function getActiveMenuTree(int $menuId, ?Carbon $datetime = null): Collection;

    /**
     * Get active menus
     */
    public function getActiveMenus(int $menuId, ?Carbon $datetime = null): Collection;

    /**
     * Find menu by reference
     */
    public function findByReference(string $reference): ?Menu;

    /**
     * Get menus by reference
     */
    public function getMenusByReference(string $reference): SupportCollection;
}
