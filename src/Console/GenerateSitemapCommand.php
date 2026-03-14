<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Services\SitemapService;
use Illuminate\Console\Command;

/**
 * Command to generate sitemap.xml
 *
 * Can be run manually or scheduled to regenerate periodically
 */
final class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate
                           {--force : Force regeneration by clearing cache first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate XML sitemap for SEO (regenerates cached sitemap)';

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): int
    {
        $force = $this->option('force');

        if ($force) {
            $this->info('🗑️  Clearing sitemap cache...');
            $sitemapService->clearCache();
        }

        $this->info('🗺️  Generating sitemaps...');

        // Generate main sitemap index
        $startTime    = microtime(true);
        $xml          = $sitemapService->generateXml();
        $endTime      = microtime(true);
        $duration     = round(($endTime - $startTime) * 1000, 2);
        $sitemapCount = substr_count($xml, '<sitemap>');

        $this->info('✅ Main sitemap index generated!');
        $this->line("   Sub-sitemaps referenced: {$sitemapCount}");
        $this->line("   Generation time: {$duration}ms");
        $this->line('   Size: ' . number_format(strlen($xml)) . ' bytes');

        // Generate page sitemap
        $startTime    = microtime(true);
        $pageXml      = $sitemapService->generatePageXml();
        $endTime      = microtime(true);
        $duration     = round(($endTime - $startTime) * 1000, 2);
        $pageUrlCount = substr_count($pageXml, '<url>');

        $this->info('✅ Page sitemap generated!');
        $this->line("   URLs included: {$pageUrlCount}");
        $this->line("   Generation time: {$duration}ms");
        $this->line('   Size: ' . number_format(strlen($pageXml)) . ' bytes');

        // Generate article sitemap
        $startTime       = microtime(true);
        $articleXml      = $sitemapService->generateArticleXml();
        $endTime         = microtime(true);
        $duration        = round(($endTime - $startTime) * 1000, 2);
        $articleUrlCount = substr_count($articleXml, '<url>');

        $this->info('✅ Article sitemap generated!');
        $this->line("   URLs included: {$articleUrlCount}");
        $this->line("   Generation time: {$duration}ms");
        $this->line('   Size: ' . number_format(strlen($articleXml)) . ' bytes');

        // Generate product sitemap
        $startTime       = microtime(true);
        $productXml      = $sitemapService->generateProductXml();
        $endTime         = microtime(true);
        $duration        = round(($endTime - $startTime) * 1000, 2);
        $productUrlCount = substr_count($productXml, '<url>');

        $this->info('✅ Product sitemap generated!');
        $this->line("   URLs included: {$productUrlCount}");
        $this->line("   Generation time: {$duration}ms");
        $this->line('   Size: ' . number_format(strlen($productXml)) . ' bytes');

        $totalUrls = $pageUrlCount + $articleUrlCount + $productUrlCount;
        $this->newLine();
        $this->info("📊 Summary: {$totalUrls} total URLs across {$sitemapCount} sub-sitemaps");

        return Command::SUCCESS;
    }
}
