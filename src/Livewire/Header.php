<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Livewire;

use Appsolutely\AIO\Services\Contracts\MenuServiceInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;

final class Header extends GeneralBlock
{
    public Collection $mainNavigation;

    public Collection $authMenuItems;

    protected array $defaultQueryOptions = [
        'main_navigation' => 'main-navigation',
        'auth_menu'       => 'auth-menu',
    ];

    protected function initializeComponent(Container $container): void
    {
        // Resolve MenuService from container (Livewire doesn't support constructor injection)
        $menuService = $container->make(MenuServiceInterface::class);

        // Initialize empty collections
        $this->mainNavigation = $menuService->getMenusByReference($this->queryOptions['main_navigation']);
        $this->authMenuItems  = $menuService->getMenusByReference($this->queryOptions['auth_menu']);
    }
}
