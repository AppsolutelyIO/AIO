<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Livewire;

use Appsolutely\AIO\Services\Contracts\MenuServiceInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;

final class Footer extends GeneralBlock
{
    public Collection $footerMenuItems;

    public Collection $socialMediaItems;

    public Collection $policyMenuItems;

    protected array $defaultQueryOptions = [
        'footer_menu'  => 'footer-menu',
        'social_media' => 'social-media',
        'policy_menu'  => 'policy-menu',
    ];

    protected function initializeComponent(Container $container): void
    {
        // Resolve MenuService from container (Livewire doesn't support constructor injection)
        $menuService = $container->make(MenuServiceInterface::class);

        $this->footerMenuItems  = $menuService->getMenusByReference($this->queryOptions['footer_menu']);
        $this->socialMediaItems = $menuService->getMenusByReference($this->queryOptions['social_media']);
        $this->policyMenuItems  = $menuService->getMenusByReference($this->queryOptions['policy_menu']);
    }
}
