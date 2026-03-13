<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Constants\BasicConstant;
use Appsolutely\AIO\Repositories\PageBlockRepository;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Services\Contracts\PageStructureServiceInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Service for generating page builder structure
 *
 * This service handles the creation of default page structures for the GrapesJS
 * page builder. It manages:
 *
 * - Generating the initial GrapesJS structure (pages, frames, components)
 * - Attaching global blocks to new pages
 * - Creating the wrapper component structure
 *
 * Separated from PageService to follow Single Responsibility Principle.
 */
final readonly class PageStructureService implements PageStructureServiceInterface
{
    public function __construct(
        protected PageBlockRepository $pageBlockRepository,
        protected PageBlockSettingRepository $pageBlockSettingRepository
    ) {}

    /**
     * Generate default page setting structure
     * Moved from Page model to service for better separation of concerns
     */
    public function generateDefaultPageSetting(): array
    {
        $structure  = $this->getPageStructure();
        $components = $this->attachGlobalBlocks();
        Arr::set($structure, BasicConstant::PAGE_GRAPESJS_KEY, $components);

        return $structure;
    }

    /**
     * Get the basic page builder structure without components
     * Moved from Page model to service
     *
     * This creates the initial GrapesJS structure with:
     * - A single page with main type
     * - One frame containing the root wrapper component
     * - Empty arrays for assets, styles, symbols, and data sources
     */
    public function getPageStructure(): array
    {
        return [
            'pages' => [
                [
                    'id'     => Str::random(16), // Unique page identifier
                    'type'   => 'main', // Main page type (GrapesJS convention)
                    'frames' => [
                        [
                            'id'        => Str::random(16), // Unique frame identifier
                            'component' => [
                                'head' => [
                                    'type' => 'head', // HTML head section
                                ],
                                'type'  => 'wrapper', // Root wrapper component type
                                'docEl' => [
                                    'tagName' => 'html', // Root HTML element
                                ],
                                // CSS properties that can be styled in the editor
                                'stylable' => [
                                    'background',
                                    'background-color',
                                    'background-image',
                                    'background-repeat',
                                    'background-attachment',
                                    'background-position',
                                    'background-size',
                                ],
                                'reference'  => 'wrapper-' . Str::random(7), // Unique reference for this wrapper
                                'components' => [], // Empty - components will be added later
                            ],
                        ],
                    ],
                ],
            ],
            'assets'      => [], // Media assets (images, videos, etc.)
            'styles'      => [], // Custom CSS styles
            'symbols'     => [], // Reusable component symbols
            'dataSources' => [], // External data source configurations
        ];
    }

    /**
     * Attach global blocks to the page structure
     * Moved from Page model to service
     */
    public function attachGlobalBlocks(): array
    {
        $blockIds     = $this->pageBlockSettingRepository->getGlobalBlockIds();
        $globalBlocks = $this->pageBlockRepository->getGlobalBlocksByIds($blockIds->toArray());

        return $globalBlocks->map(function ($block) {
            return [
                'type'      => $block->reference,
                'block_id'  => $block->id,
                'droppable' => $block->droppable ?? 0,
                'reference' => $block->reference . '-' . Str::random(7),
            ];
        })->toArray();
    }
}
