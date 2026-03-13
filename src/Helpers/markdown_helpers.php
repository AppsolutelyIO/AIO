<?php

declare(strict_types=1);

use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

if (! function_exists('parse_markdown_images')) {
    function parse_markdown_images(?string $markdown): array
    {
        try {
            if (empty($markdown)) {
                return [];
            }
            // Create a new environment
            $environment = new Environment([
                'html_input'         => 'allow',
                'allow_unsafe_links' => false,
            ]);
            $environment->addExtension(new CommonMarkCoreExtension());

            // Create the converter
            $converter = new MarkdownConverter($environment);

            // Convert markdown to HTML
            $html = $converter->convert($markdown)->getContent();

            // Use DOMDocument to parse the HTML and extract image attributes
            $dom = new \DOMDocument();
            @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $images = [];
            foreach ($dom->getElementsByTagName('img') as $img) {
                $attributes = [];
                foreach ($img->attributes as $attr) {
                    $attributes[$attr->nodeName] = $attr->nodeValue;
                }
                $images[] = [
                    'url'   => $attributes['src'] ?? '',
                    'alt'   => $attributes['alt'] ?? '',
                    'title' => $attributes['title'] ?? '',
                ];
            }

            return $images;
        } catch (\Exception $e) {
            log_error($e->getMessage(), null, __CLASS__, __FUNCTION__);

            return [];
        }
    }
}

if (! function_exists('md2html')) {
    /**
     * Convert markdown text to HTML
     *
     * @param  string  $text  The markdown text to convert
     * @return string The converted HTML, or original text on error
     */
    function md2html(string $text): string
    {
        $text = trim($text);

        if (empty($text)) {
            return $text;
        }

        try {
            static $converter = null;

            if ($converter === null) {
                $environment = new Environment([
                    'footnote' => [
                        'backref_class'      => 'footnote-backref',
                        'backref_symbol'     => '↩',
                        'container_add_hr'   => true,
                        'container_class'    => 'footnotes',
                        'ref_class'          => 'footnote-ref',
                        'ref_id_prefix'      => 'fnref:',
                        'footnote_class'     => 'footnote',
                        'footnote_id_prefix' => 'fn:',
                    ],
                ]);
                $environment->addExtension(new CommonMarkCoreExtension());
                $environment->addExtension(new GithubFlavoredMarkdownExtension());
                $environment->addExtension(new FootnoteExtension());
                $converter = new MarkdownConverter($environment);
            }

            $html = $converter->convert($text)->getContent();

            return $html;
        } catch (CommonMarkException $e) {
            log_warning('Unable to parse markdown text.', ['exception' => $e->getMessage(), 'text' => $text]);

            return $text;
        }
    }
}
