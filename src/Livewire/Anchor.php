<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Livewire;

use Appsolutely\AIO\Enums\BlockScope;
use Illuminate\Contracts\Container\Container;

/**
 * Anchor block: sticky navigation bar linking to sections below.
 *
 * Shows only page-scoped blocks after itself (excludes global blocks like footer).
 * Only includes blocks that have anchor_label (from page_block_values.anchor_label column).
 */
final class Anchor extends GeneralBlock
{
    public int $blockSort = 0;

    /**
     * @var array<int, array{sort: int, reference: string, scope: string, view: string, anchor_label: string|null, display_options: array, block_title: string}>
     */
    public array $blocksForAnchor = [];

    /**
     * @var array<int, array{reference: string, title: string}>
     */
    public array $anchorItems = [];

    protected function initializeComponent(Container $container): void
    {
        $blocks = $this->blocksForAnchor;
        if (empty($blocks) || $this->blockSort <= 0) {
            $this->anchorItems = [];

            return;
        }

        $items = [];

        foreach ($blocks as $block) {
            $sort  = (int) ($block['sort'] ?? 0);
            $scope = $block['scope'] ?? BlockScope::Page->value;

            if ($scope !== BlockScope::Page->value || $sort <= $this->blockSort) {
                continue;
            }

            $reference   = (string) ($block['reference'] ?? '');
            $anchorLabel = $this->resolveAnchorLabel($block);

            if ($anchorLabel !== '' && $reference !== '') {
                $items[] = [
                    'reference' => $reference,
                    'title'     => $anchorLabel,
                ];
            }
        }

        $this->anchorItems = $items;
    }

    /**
     * Resolve anchor label from block data (page_block_values.anchor_label column).
     */
    private function resolveAnchorLabel(array $block): string
    {
        $anchorLabel = $block['anchor_label'] ?? null;

        return $anchorLabel !== null && $anchorLabel !== '' ? trim((string) $anchorLabel) : '';
    }

    protected function getExtraData(): array
    {
        return [
            'anchorItems' => $this->anchorItems,
        ];
    }
}
