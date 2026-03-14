<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders\Admin;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\PageBlock;
use Appsolutely\AIO\Models\PageBlockGroup;
use Illuminate\Database\Seeder;

final class PageBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create groups first
        $this->createGroups();

        // Then create blocks
        $layoutGroup  = PageBlockGroup::where('title', 'Navigation')->first();
        $contentGroup = PageBlockGroup::where('title', 'Content')->first();

        if ($layoutGroup) {
            $this->createHeaderFooterBlocks($layoutGroup);
        }

        if ($contentGroup) {
            $this->createContentBlocks($contentGroup);
        }
    }

    /**
     * Create page block groups.
     */
    private function createGroups(): void
    {
        $groups = [
            [
                'title'  => 'Navigation',
                'remark' => 'Navigation blocks like header and footer',
                'status' => Status::ACTIVE,
                'sort'   => 1,
            ],
            [
                'title'  => 'Content',
                'remark' => 'Content blocks for page sections',
                'status' => Status::ACTIVE,
                'sort'   => 2,
            ],
        ];

        foreach ($groups as $group) {
            PageBlockGroup::firstOrCreate(
                ['title' => $group['title']],
                $group
            );
        }
    }

    /**
     * Create header and footer blocks.
     */
    private function createHeaderFooterBlocks(PageBlockGroup $group): void
    {
        // Header
        PageBlock::firstOrCreate(
            ['class' => 'App\\Livewire\\Header', 'block_group_id' => $group->id],
            array_merge(
                $this->getBasicFields(),
                [
                    'title'                    => 'Header',
                    'class'                    => 'App\\Livewire\\Header',
                    'template'                 => $this->getTemplate('header'),
                    'description'              => 'Main site header with navigation and logo',
                    'sort'                     => 1,
                    'reference'                => 'header',
                    'scope'                    => 'global',
                    'query_options'            => $this->getHeaderQueryOptions(),
                    'query_options_definition' => $this->getHeaderQueryOptionsDefinition(),
                ]
            )
        );

        // Footer
        PageBlock::firstOrCreate(
            ['class' => 'App\\Livewire\\Footer', 'block_group_id' => $group->id],
            array_merge(
                $this->getBasicFields(),
                [
                    'title'                    => 'Footer',
                    'class'                    => 'App\\Livewire\\Footer',
                    'template'                 => $this->getTemplate('footer'),
                    'description'              => 'Site footer with links, social media, and company information',
                    'sort'                     => 2,
                    'reference'                => 'footer',
                    'scope'                    => 'global',
                    'query_options'            => $this->getFooterQueryOptions(),
                    'query_options_definition' => $this->getFooterQueryOptionsDefinition(),
                ]
            )
        );
    }

    /**
     * Create content blocks.
     */
    private function createContentBlocks(PageBlockGroup $group): void
    {
        // General Block
        PageBlock::firstOrCreate(
            ['class' => 'App\\Livewire\\GeneralBlock', 'block_group_id' => $group->id],
            array_merge(
                $this->getBasicFields(),
                [
                    'title'       => 'General Block',
                    'class'       => 'App\\Livewire\\GeneralBlock',
                    'template'    => $this->getTemplate('general-block'),
                    'description' => 'Base Livewire block.',
                    'sort'        => 1,
                    'reference'   => 'general-block',
                ]
            )
        );

        // Article List
        PageBlock::firstOrCreate(
            ['class' => 'App\\Livewire\\ArticleList', 'block_group_id' => $group->id],
            array_merge(
                $this->getBasicFields(),
                [
                    'title'                    => 'Article List',
                    'class'                    => 'App\\Livewire\\ArticleList',
                    'template'                 => $this->getTemplate('article-list'),
                    'description'              => 'Display a list of articles with customizable layout and filtering options.',
                    'sort'                     => 2,
                    'reference'                => 'article-list',
                    'query_options'            => $this->getArticleListQueryOptions(),
                    'query_options_definition' => $this->getArticleListQueryOptionsDefinition(),
                ]
            )
        );

        // Dynamic Form
        PageBlock::firstOrCreate(
            ['class' => 'App\\Livewire\\DynamicForm', 'block_group_id' => $group->id],
            array_merge(
                $this->getBasicFields(),
                [
                    'title'                    => 'Dynamic Form',
                    'class'                    => 'App\\Livewire\\DynamicForm',
                    'template'                 => $this->getTemplate('dynamic-form'),
                    'description'              => 'Database-driven form component that pulls form configuration from the database.',
                    'sort'                     => 3,
                    'reference'                => 'dynamic-form',
                    'query_options'            => $this->getDynamicFormQueryOptions(),
                    'query_options_definition' => $this->getDynamicFormQueryOptionsDefinition(),
                ]
            )
        );

        // Anchor
        PageBlock::firstOrCreate(
            ['class' => 'App\\Livewire\\Anchor', 'block_group_id' => $group->id],
            array_merge(
                $this->getBasicFields(),
                [
                    'title'       => 'Anchor Navigation',
                    'class'       => 'App\\Livewire\\Anchor',
                    'template'    => $this->getTemplate('anchor'),
                    'description' => 'Sticky navigation bar linking to sections below. Shows only page blocks after itself.',
                    'sort'        => 4,
                    'reference'   => 'anchor',
                ]
            )
        );
    }

    /**
     * Get basic fields for page blocks.
     */
    private function getBasicFields(): array
    {
        return [
            'instruction' => null,
            'schema'      => [],
            'setting'     => [],
            'droppable'   => 0,
            'status'      => Status::ACTIVE,
            'scope'       => 'page',
        ];
    }

    private function getHeaderQueryOptions(): array
    {
        return [
            'main_navigation' => 'main-navigation',
            'auth_menu'       => 'auth-menu',
        ];
    }

    private function getHeaderQueryOptionsDefinition(): array
    {
        return [
            'main_navigation' => [
                'type'    => 'text',
                'label'   => 'Main Navigation Reference',
                'default' => 'main-navigation',
            ],
            'auth_menu' => [
                'type'    => 'text',
                'label'   => 'Auth Menu Reference',
                'default' => 'auth-menu',
            ],
        ];
    }

    private function getFooterQueryOptions(): array
    {
        return [
            'footer_menu'  => 'footer-menu',
            'social_media' => 'social-media',
            'policy_menu'  => 'policy-menu',
        ];
    }

    private function getFooterQueryOptionsDefinition(): array
    {
        return [
            'footer_menu' => [
                'type'    => 'text',
                'label'   => 'Footer Menu Reference',
                'default' => 'footer-menu',
            ],
            'social_media' => [
                'type'    => 'text',
                'label'   => 'Social Media Reference',
                'default' => 'social-media',
            ],
            'policy_menu' => [
                'type'    => 'text',
                'label'   => 'Policy Menu Reference',
                'default' => 'policy-menu',
            ],
        ];
    }

    private function getArticleListQueryOptions(): array
    {
        return [
            'posts_per_page'  => 6,
            'category_filter' => '',
            'tag_filter'      => '',
            'order_by'        => 'published_at',
            'order_direction' => 'desc',
        ];
    }

    private function getArticleListQueryOptionsDefinition(): array
    {
        return [
            'posts_per_page' => [
                'type'    => 'number',
                'label'   => 'Posts Per Page',
                'min'     => 1,
                'max'     => 50,
                'default' => 6,
            ],
            'category_filter' => [
                'type'    => 'text',
                'label'   => 'Category Filter',
                'default' => '',
            ],
            'tag_filter' => [
                'type'    => 'text',
                'label'   => 'Tag Filter',
                'default' => '',
            ],
            'order_by' => [
                'type'    => 'select',
                'label'   => 'Order By',
                'options' => [
                    ['value' => 'published_at', 'label' => 'Published Date'],
                    ['value' => 'title', 'label' => 'Title'],
                    ['value' => 'created_at', 'label' => 'Created Date'],
                ],
                'default' => 'published_at',
            ],
            'order_direction' => [
                'type'    => 'select',
                'label'   => 'Order Direction',
                'options' => [
                    ['value' => 'asc', 'label' => 'Ascending'],
                    ['value' => 'desc', 'label' => 'Descending'],
                ],
                'default' => 'desc',
            ],
        ];
    }

    private function getDynamicFormQueryOptions(): array
    {
        return [
            'form_slug' => '',
        ];
    }

    private function getDynamicFormQueryOptionsDefinition(): array
    {
        return [
            'form_slug' => [
                'type'        => 'text',
                'label'       => 'Form Slug',
                'description' => 'Reference to the dynamic form to render',
                'default'     => '',
            ],
        ];
    }

    /**
     * Get template for block.
     */
    private function getTemplate(string $reference): string
    {
        static $templates = null;
        if ($templates === null) {
            $path = __DIR__ . '/page_block_templates.json';
            if (file_exists($path)) {
                $json      = file_get_contents($path);
                $templates = json_decode($json, true);
            } else {
                $templates = [];
            }
        }

        return $templates[$reference]
            ?? '<div class="block-content"><div class="container"><div class="row"><div class="col-12"><h2>This is ' . htmlspecialchars($reference) . ' Block</h2><p>Block content goes here. Customize this template as needed.</p></div></div></div></div>';
    }
}
