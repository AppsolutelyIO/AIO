<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Article;

interface ArticleServiceInterface
{
    /**
     * Get content summary for an article
     */
    public function getContentSummary(Article $article): string;

    /**
     * Get formatted content (Markdown to HTML)
     */
    public function getFormattedContent(Article $article): ?string;
}
