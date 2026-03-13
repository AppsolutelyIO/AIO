<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers\Api;

use Appsolutely\AIO\Repositories\MenuRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MenuApiController extends AdminBaseApiController
{
    public function __construct(protected MenuRepository $menuRepository) {}

    /**
     * Check for active menus that reference a given page slug.
     */
    public function activeMenusByPageSlug(Request $request): JsonResponse
    {
        $slug = $request->query('slug', '');

        if (empty($slug)) {
            return $this->success(['menus' => [], 'count' => 0]);
        }

        $activeMenus = $this->menuRepository->findActiveMenusByPageSlug($slug);

        return $this->success([
            'menus' => $activeMenus->map(fn ($menu) => [
                'id'    => $menu->id,
                'title' => $menu->title,
                'url'   => $menu->url,
            ])->values()->toArray(),
            'count' => $activeMenus->count(),
        ]);
    }
}
