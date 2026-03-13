<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface PageStructureServiceInterface
{
    /**
     * Generate default page setting structure with global blocks
     *
     * @return array The complete page structure ready for GrapesJS
     */
    public function generateDefaultPageSetting(): array;

    /**
     * Get the basic page builder structure without components
     *
     * @return array The basic GrapesJS structure
     */
    public function getPageStructure(): array;

    /**
     * Attach global blocks to the page structure
     *
     * @return array Array of global block components
     */
    public function attachGlobalBlocks(): array;
}
