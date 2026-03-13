<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders;

use Appsolutely\AIO\Enums\MenuTarget;
use Appsolutely\AIO\Enums\MenuType;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create menu first
        $this->createRoots();

        // Then create menu
        $mainMenu   = Menu::where('title', 'Main Navigation')->first();
        $footerMenu = Menu::where('title', 'Footer Menu')->first();
        $userMenu   = Menu::where('title', 'User Menu')->first();
        $policyMenu = Menu::where('title', 'Policy menu')->first();
        $socialMenu = Menu::where('title', 'Social Media')->first();

        if ($mainMenu) {
            $this->createMainNavigation($mainMenu);
        }

        if ($footerMenu) {
            $this->createFooterMenu($footerMenu);
        }

        if ($userMenu) {
            $this->createUserMenu($userMenu);
        }

        if ($policyMenu) {
            $this->createPolicyMenu($policyMenu);
        }

        if ($socialMenu) {
            $this->createSocialMediaMenu($socialMenu);
        }
    }

    protected function createMenuFromTree(array $menus, ?Menu $parent = null): void
    {
        foreach ($menus as $item) {
            // Prepare data for creation
            $data = [
                'parent_id'    => $parent?->id,
                'title'        => $item['title'],
                'remark'       => $item['remark'] ?? null,
                'url'          => $item['url'] ?? '',
                'type'         => MenuType::Link->value ?? null,
                'icon'         => $item['icon'] ?? '',
                'target'       => MenuTarget::Self->value ?? null,
                'is_external'  => $item['is_external'] ?? 0,
                'published_at' => now(),
                'status'       => Status::ACTIVE,
            ];

            if (Menu::where('title', $data['title'])->where('parent_id', $data['parent_id'])->exists()) {
                continue;
            }

            // Create the menu
            if ($parent) {
                // This is a child, append it to the parent
                $menu = new Menu($data);
                $menu->appendToNode($parent)->save();
            } else {
                // This is a root, create it as root
                $menu = Menu::create($data);
            }

            // If this has children, create them recursively
            if (isset($item['children']) && is_array($item['children'])) {
                $this->createMenuFromTree($item['children'], $menu);
            }
        }
    }

    protected function createRoots(): void
    {
        $menus = $this->getRoots();
        $this->createMenuFromTree($menus);
    }

    protected function getRoots(): array
    {
        return [
            [
                'title'  => 'Main Navigation',
                'remark' => 'Primary navigation menu for the website',
            ],
            [
                'title'  => 'Footer Menu',
                'remark' => 'Footer navigation links',
            ],
            [
                'title'  => 'User Menu',
                'remark' => 'User-related links',
            ],
            [
                'title'  => 'Social Media',
                'remark' => 'Social media navigation links',
            ],
            [
                'title'  => 'Policy menu',
                'remark' => 'Policy links',
            ],
        ];

    }

    protected function createMainNavigation(Menu $parent): void
    {
        $menu = $this->getMainNavigation();
        $this->createMenuFromTree($menu, $parent);
    }

    protected function getMainNavigation(): array
    {
        return [
            [
                'title' => 'Home',
                'url'   => '/',
            ],
            [
                'title' => 'About',
                'url'   => '/about',
            ],
            [
                'title'    => 'Services',
                'url'      => '/services',
                'children' => [
                    [
                        'title' => 'Web Development',
                        'url'   => '/services/web-development',
                    ],
                    [
                        'title' => 'Mobile Apps',
                        'url'   => '/services/mobile-apps',
                    ],
                ],
            ],
            [
                'title' => 'Contact',
                'url'   => '/contact',
            ],
        ];
    }

    protected function createFooterMenu(Menu $parent): void
    {
        $menu = $this->getFooterMenu();
        $this->createMenuFromTree($menu, $parent);
    }

    protected function getFooterMenu(): array
    {
        return [];
    }

    protected function createUserMenu(Menu $parent): void
    {
        $menu = $this->getUserMenu();
        $this->createMenuFromTree($menu, $parent);
    }

    protected function getUserMenu(): array
    {
        return [];
    }

    protected function createPolicyMenu(Menu $parent): void
    {
        $menu = $this->getPolicyMenu();
        $this->createMenuFromTree($menu, $parent);
    }

    protected function getPolicyMenu(): array
    {
        return [
            [
                'title' => 'Terms of Service',
                'url'   => '/terms-of-service',
            ],
            [
                'title' => 'Privacy Policy',
                'url'   => '/privacy-policy',
            ],
            [
                'title' => 'Cookie Policy',
                'url'   => '/cookie-policy',
            ],
        ];
    }

    protected function createSocialMediaMenu(Menu $parent): void
    {
        $menu = $this->getSocialMediaMenu();
        $this->createMenuFromTree($menu, $parent);
    }

    protected function getSocialMediaMenu(): array
    {
        return [
            [
                'title'       => 'TikTok',
                'url'         => 'https://www.tiktok.com/@yourcompany',
                'icon'        => 'bi bi-tiktok',
                'is_external' => true,
            ],
            [
                'title'       => 'Facebook',
                'url'         => 'https://www.facebook.com/yourcompany',
                'icon'        => 'bi bi-facebook',
                'is_external' => true,
            ],
            [
                'title'       => 'Twitter',
                'url'         => 'https://twitter.com/yourcompany',
                'icon'        => 'bi bi-twitter-x',
                'is_external' => true,
            ],
            [
                'title'       => 'YouTube',
                'url'         => 'https://www.youtube.com/@yourcompany',
                'icon'        => 'bi bi-youtube',
                'is_external' => true,
            ],
        ];
    }
}
