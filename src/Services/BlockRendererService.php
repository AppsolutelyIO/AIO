<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Livewire\Anchor;
use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Models\PageBlockValue;
use Appsolutely\AIO\Services\Concerns\ResolvesLivewireClassName;
use Appsolutely\AIO\Services\Contracts\BlockRendererServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Livewire;

use function Livewire\invade;

final readonly class BlockRendererService implements BlockRendererServiceInterface
{
    use ResolvesLivewireClassName;

    /**
     * Validate and render a block safely
     * Returns the rendered HTML or error message
     */
    public function renderBlockSafely(PageBlockSetting $block, GeneralPage $page): string
    {
        // Validate block structure
        if (! isset($block['block']['class']) || ! isset($block['reference'])) {
            return $this->getBlockErrorHtml('Invalid block structure');
        }

        $className = ! empty($block['block']['class']) ? $block['block']['class'] : 'Appsolutely\AIO\Livewire\GeneralBlock';
        $className = $this->resolveClassName($className);
        $reference = $block['reference'];

        // Validate class exists
        if (! class_exists($className)) {
            return $this->getBlockErrorHtml("Class '{$className}' not found");
        }

        // Validate reference is not empty
        if (empty($reference)) {
            return $this->getBlockErrorHtml('Reference is empty');
        }

        // Validate it's a Livewire component
        if (! is_subclass_of($className, Component::class)) {
            return $this->getBlockErrorHtml("Class '{$className}' is not a Livewire component");
        }

        $blockValue = $block->blockValue ?? null;

        // Check publish/expire dates before mounting the Livewire component
        if ($blockValue && ! $this->isBlockValueVisible($blockValue)) {
            return '';
        }

        $viewName       = $blockValue->view ?? '';
        $viewStyle      = ($blockValue->view_style !== null && $blockValue->view_style !== '')
            ? (string) $blockValue->view_style
            : 'default';
        $queryOptions   = $block->queryOptionsValue ?? [];
        $displayOptions = $block->displayOptionsValue ?? [];
        $pageData       = $page->toArray();

        $data = [
            'page'           => $pageData,
            'viewName'       => $viewName,
            'viewStyle'      => $viewStyle,
            'queryOptions'   => $queryOptions,
            'displayOptions' => $displayOptions,
            'blockSort'      => (int) ($block->sort ?? 0),
        ];

        if ($className === Anchor::class) {
            $data['blocksForAnchor'] = $this->buildBlocksForAnchor($page);
        }

        // Save View Factory state before rendering.
        // If Livewire::mount() throws, Laravel's View::render() catch block
        // calls flushState() which clears the sectionStack — destroying any
        // parent @section that is still open (e.g., in pages/show.blade.php).
        $factory      = view();
        $sectionStack = invade($factory)->sectionStack;
        $sections     = invade($factory)->sections;
        $renderCount  = invade($factory)->renderCount;

        try {
            return Livewire::mount($className, $data, $reference);
        } catch (\Throwable $e) {
            // Restore the View Factory state so the parent view can continue.
            invade($factory)->sectionStack = $sectionStack;
            invade($factory)->sections     = $sections;
            invade($factory)->renderCount  = $renderCount;

            return $this->getBlockErrorHtml($e->getMessage());
        }
    }

    /**
     * Check if a block value is currently visible based on its publish/expire dates.
     */
    private function isBlockValueVisible(PageBlockValue $blockValue): bool
    {
        $adminGuard = config('admin.auth.guard', 'admin');
        if (Auth::guard($adminGuard)->check()) {
            return true;
        }

        $publishedAt = $blockValue->published_at;
        $expiredAt   = $blockValue->expired_at;

        // No published_at means always visible
        if (! $publishedAt) {
            return true;
        }

        $now = Carbon::now();

        // Not yet published
        if ($now->lt($publishedAt)) {
            return false;
        }

        // No expiry or not yet expired
        return ! $expiredAt || $now->lte($expiredAt);
    }

    /**
     * Build minimal block data for anchor navigation (used by Anchor block).
     *
     * @return array<int, array{sort: int, reference: string, scope: string, view: string, anchor_label: string|null, display_options: array, block_title: string}>
     */
    private function buildBlocksForAnchor(GeneralPage $page): array
    {
        $blocks = $page->blocks ?? collect();

        return collect($blocks)->map(function ($b) {
            $anchorLabel = $b->blockValue?->anchor_label;

            return [
                'sort'            => (int) ($b->sort ?? 0),
                'reference'       => (string) ($b->reference ?? ''),
                'scope'           => (string) ($b->block?->scope ?? 'page'),
                'view'            => (string) ($b->blockValue?->view ?? ''),
                'anchor_label'    => $anchorLabel !== null && $anchorLabel !== '' ? (string) $anchorLabel : null,
                'display_options' => $b->displayOptionsValue ?? [],
                'block_title'     => (string) ($b->block?->title ?? ''),
            ];
        })->values()->toArray();
    }

    /**
     * Get HTML for block errors (only in debug mode)
     */
    private function getBlockErrorHtml(string $message): string
    {
        if (! app()->isProduction()) {
            return "<div class='alert alert-danger'><strong>Block Error:</strong> {$message}</div>";
        }

        return '';
    }
}
