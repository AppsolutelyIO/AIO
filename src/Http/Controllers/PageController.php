<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers;

use Appsolutely\AIO\Services\Contracts\GeneralPageServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class PageController extends BaseController
{
    public function __construct(
        private readonly GeneralPageServiceInterface $generalPageService
    ) {}

    public function show(Request $request, ?string $slug = null): View
    {
        $page = $this->generalPageService->resolvePageWithCaching($slug);

        if (! $page) {
            abort(404);
        }

        return themed_view('pages.show', [
            'page' => $page,
        ]);
    }
}
