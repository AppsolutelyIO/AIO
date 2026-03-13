<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers;

use Appsolutely\AIO\Services\SitemapService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Controller for sitemap.xml generation
 *
 * Follows architecture patterns:
 * - Final class
 * - Dependency injection via constructor
 * - Service layer for business logic
 */
final class SitemapController extends BaseController
{
    public function __construct(
        private readonly SitemapService $sitemapService
    ) {}

    /**
     * Generate and return sitemap.xml (sitemap index)
     *
     * Query parameter: ?force_update=1 to force cache regeneration
     */
    public function index(Request $request): Response
    {
        // Check for force_update parameter
        if ($request->query('force_update') === '1') {
            $this->sitemapService->clearCache();
        }

        $xml = $this->sitemapService->generateXml();

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Generate and return sitemap for a specific type (article or product)
     *
     * Query parameter: ?force_update=1 to force cache regeneration
     */
    public function type(Request $request, string $type): Response
    {
        // Check for force_update parameter
        if ($request->query('force_update') === '1') {
            $this->sitemapService->clearCache();
        }

        $xml = $this->sitemapService->generateTypeXml($type);

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
