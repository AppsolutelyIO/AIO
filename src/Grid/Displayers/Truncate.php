<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Grid\Displayers;

/**
 * CSS-based text truncation displayer.
 *
 * Wraps content in a block with CSS `text-overflow: ellipsis`.
 * Unlike `Limit` which outputs toggle HTML and conflicts with other displayers,
 * this simply constrains the visual width while preserving the inner content.
 *
 * Place this AFTER other displayers in the chain (e.g. `->copyable()->truncate()`).
 */
class Truncate extends AbstractDisplayer
{
    public function display(string $maxWidth = '150px'): string
    {
        if ($this->value === '' || $this->value === null) {
            return '';
        }

        return <<<HTML
<div style="max-width:{$maxWidth};overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{$this->row->{$this->column->getName()}}">{$this->value}</div>
HTML;
    }
}
