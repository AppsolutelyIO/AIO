<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers;

use Appsolutely\AIO\Services\Contracts\GeneralPageServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PageController extends BaseController
{
    public function __construct(
        private readonly GeneralPageServiceInterface $generalPageService
    ) {}

    public function show(Request $request, ?string $slug = null): View|RedirectResponse
    {
        $page = $this->generalPageService->resolvePageWithCaching($slug);

        if (! $page) {
            abort(404);
        }

        // Alias URL without ?submitted=1 → redirect to canonical page
        if ($page->getPageAlias() !== null && $request->query('submitted') !== '1') {
            return redirect(normalize_slug($page->getContent()->getAttribute('slug')));
        }

        return themed_view('pages.show', [
            'page' => $page,
        ]);
    }
}
