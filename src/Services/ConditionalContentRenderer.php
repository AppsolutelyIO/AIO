<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Services\Contracts\ConditionalContentRendererInterface;
use Illuminate\Http\Request;

final readonly class ConditionalContentRenderer implements ConditionalContentRendererInterface
{
    public function __construct(
        private Request $request,
    ) {}

    public function render(string $content): string
    {
        if (! str_contains($content, '@if(')) {
            return $content;
        }

        return $this->processBlocks($content);
    }

    /**
     * Iteratively find and resolve the outermost @if...@endif blocks.
     */
    private function processBlocks(string $content): string
    {
        while (($block = $this->findOutermostBlock($content)) !== null) {
            $resolved = $this->resolveBlock($block['branches']);
            $resolved = $this->processBlocks($resolved);
            $content  = substr_replace($content, $resolved, $block['start'], $block['length']);
        }

        return $content;
    }

    /**
     * Find the first outermost @if...@endif block in content.
     *
     * @return array{start: int, length: int, branches: list<array{condition: string, content: string}>}|null
     */
    private function findOutermostBlock(string $content): ?array
    {
        $ifPos = strpos($content, '@if(');
        if ($ifPos === false) {
            return null;
        }

        $depth = 0;
        $pos   = $ifPos;
        $len   = strlen($content);

        while ($pos < $len) {
            if (substr($content, $pos, 4) === '@if(') {
                $depth++;
                $pos += 4;
            } elseif (substr($content, $pos, 6) === '@endif') {
                $depth--;
                if ($depth === 0) {
                    $blockStr = substr($content, $ifPos, $pos + 6 - $ifPos);

                    return [
                        'start'    => $ifPos,
                        'length'   => $pos + 6 - $ifPos,
                        'branches' => $this->parseBranches($blockStr),
                    ];
                }
                $pos += 6;
            } else {
                $pos++;
            }
        }

        // Unmatched @if — return content as-is by stripping the orphan directive
        return null;
    }

    /**
     * Parse an @if...@endif block into branches with conditions and content.
     *
     * @return list<array{condition: string, content: string}>
     */
    private function parseBranches(string $block): array
    {
        $branches         = [];
        $depth            = 0;
        $pos              = 0;
        $len              = strlen($block);
        $currentCondition = '';
        $currentContent   = '';

        while ($pos < $len) {
            // Opening @if at depth 0 — extract condition
            if ($depth === 0 && substr($block, $pos, 4) === '@if(') {
                $depth            = 1;
                $condEnd          = $this->findClosingParen($block, $pos + 3);
                $currentCondition = substr($block, $pos + 4, $condEnd - $pos - 4);
                $pos              = $condEnd + 1;
                $currentContent   = '';

                continue;
            }

            // Nested @if — accumulate as content
            if ($depth > 1 && substr($block, $pos, 4) === '@if(') {
                $depth++;
                $currentContent .= '@if(';
                $pos += 4;

                continue;
            }
            if ($depth === 1 && substr($block, $pos, 4) === '@if(') {
                $depth++;
                $currentContent .= '@if(';
                $pos += 4;

                continue;
            }

            // @elseif at top level
            if ($depth === 1 && substr($block, $pos, 8) === '@elseif(') {
                $branches[]       = ['condition' => $currentCondition, 'content' => $currentContent];
                $condEnd          = $this->findClosingParen($block, $pos + 7);
                $currentCondition = substr($block, $pos + 8, $condEnd - $pos - 8);
                $pos              = $condEnd + 1;
                $currentContent   = '';

                continue;
            }

            // @else at top level (but not @elseif)
            if ($depth === 1 && substr($block, $pos, 5) === '@else' && substr($block, $pos, 8) !== '@elseif(') {
                $branches[]       = ['condition' => $currentCondition, 'content' => $currentContent];
                $currentCondition = '__else__';
                $pos += 5;
                $currentContent = '';

                continue;
            }

            // @endif
            if (substr($block, $pos, 6) === '@endif') {
                $depth--;
                if ($depth === 0) {
                    $branches[] = ['condition' => $currentCondition, 'content' => $currentContent];

                    break;
                }
                $currentContent .= '@endif';
                $pos += 6;

                continue;
            }

            // Regular character
            if ($depth >= 1) {
                $currentContent .= $block[$pos];
            }
            $pos++;
        }

        return $branches;
    }

    /**
     * Find the matching closing parenthesis for an opening '(' at $openPos.
     */
    private function findClosingParen(string $content, int $openPos): int
    {
        $depth = 0;
        $pos   = $openPos;
        $len   = strlen($content);

        while ($pos < $len) {
            if ($content[$pos] === '(') {
                $depth++;
            } elseif ($content[$pos] === ')') {
                $depth--;
                if ($depth === 0) {
                    return $pos;
                }
            }
            $pos++;
        }

        return $len - 1;
    }

    /**
     * Evaluate branches and return the content of the first matching condition.
     *
     * @param  list<array{condition: string, content: string}>  $branches
     */
    private function resolveBlock(array $branches): string
    {
        foreach ($branches as $branch) {
            if ($branch['condition'] === '__else__') {
                return $branch['content'];
            }

            if ($this->evaluateCondition($branch['condition'])) {
                return $branch['content'];
            }
        }

        return '';
    }

    /**
     * Safely evaluate a condition string against the whitelist.
     * Supports `!` negation prefix.
     */
    private function evaluateCondition(string $condition): bool
    {
        $condition = trim($condition);

        $negated = false;
        if (str_starts_with($condition, '! ') || str_starts_with($condition, '!')) {
            $negated   = true;
            $condition = trim(ltrim($condition, '! '));
        }

        $result = $this->matchAndEvaluate($condition);

        return $negated ? ! $result : $result;
    }

    /**
     * Match condition against whitelist patterns and evaluate.
     * Returns false for any unrecognized condition (secure by default).
     */
    private function matchAndEvaluate(string $expression): bool
    {
        // request()->is('pattern') or request()->is("pattern")
        if (preg_match('/^request\(\)->is\([\'"]([^\'"]+)[\'"]\)$/', $expression, $m)) {
            return $this->request->is($m[1]);
        }

        // request()->routeIs('name') or request()->routeIs("name")
        if (preg_match('/^request\(\)->routeIs\([\'"]([^\'"]+)[\'"]\)$/', $expression, $m)) {
            return $this->request->routeIs($m[1]);
        }

        // request()->is('a', 'b') — multiple patterns
        if (preg_match('/^request\(\)->is\((.+)\)$/', $expression, $m)) {
            $patterns = $this->parseStringArguments($m[1]);
            if ($patterns !== null) {
                return $this->request->is(...$patterns);
            }
        }

        // request()->routeIs('a', 'b') — multiple patterns
        if (preg_match('/^request\(\)->routeIs\((.+)\)$/', $expression, $m)) {
            $patterns = $this->parseStringArguments($m[1]);
            if ($patterns !== null) {
                return $this->request->routeIs(...$patterns);
            }
        }

        // Unknown condition — secure default
        return false;
    }

    /**
     * Parse comma-separated quoted string arguments.
     * Returns null if any argument is not a quoted string (safety check).
     *
     * @return list<string>|null
     */
    private function parseStringArguments(string $raw): ?array
    {
        $parts = preg_split('/\s*,\s*/', trim($raw));
        if ($parts === false) {
            return null;
        }

        $result = [];
        foreach ($parts as $part) {
            if (preg_match('/^[\'"]([^\'"]+)[\'"]$/', trim($part), $m)) {
                $result[] = $m[1];
            } else {
                return null;
            }
        }

        return $result ?: null;
    }
}
