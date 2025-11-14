<?php

namespace Zaimea\CommonMark\Timeline;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class TimelineRenderer implements NodeRendererInterface
{
    /**
     * @param Timeline $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        Timeline::assertInstanceOf($node);

        // Render all parsed children to HTML (headings, paragraphs, <hr>, etc.)
        $rendered = (string) $childRenderer->renderNodes($node->children());

        // Split into parts based on heading tags (<h3 ...>), keep only parts that have content
        $parts = preg_split('/(?=<h3\b)/i', $rendered) ?: [];

        $stepsHtml = '';

        foreach ($parts as $partHtml) {
            $partHtml = trim($partHtml);
            if ($partHtml === '') {
                continue;
            }

            // extract heading text (date) from <h3>...<\/h3>
            $headingText = null;
            if (preg_match('/<h3\b[^>]*>(.*?)<\/h3>/is', $partHtml, $m)) {
                $headingText = trim(strip_tags($m[1]));
                // remove the heading from the part so the rest is body/meta
                $partHtml = preg_replace('/<h3\b[^>]*>.*?<\/h3>/is', '', $partHtml, 1);
            }

            // extract metadata lines that are present as paragraphs containing "@key: value"
            $icon = null;
            $link = null;
            $cta  = null;

            // Try to extract from raw HTML paragraphs like <p>@icon: academic-cap</p>
            if (preg_match_all('/<p\b[^>]*>\s*@(\w+)\s*:\s*([^<\r\n]+)\s*<\/p>/is', $partHtml, $metaMatches, PREG_SET_ORDER)) {
                foreach ($metaMatches as $mm) {
                    $key = strtolower(trim($mm[1]));
                    $val = trim($mm[2]);
                    if ($key === 'icon') {
                        $icon = $val;
                    } elseif ($key === 'link') {
                        $link = $val;
                    } elseif ($key === 'cta') {
                        $cta = $val;
                    }
                }
                // remove all those meta paragraphs from the part HTML
                $partHtml = preg_replace('/<p\b[^>]*>\s*@\w+\s*:\s*[^<\r\n]+<\/p>\s*/is', '', $partHtml);
            } else {
                // fallback: maybe metadata present as plain text lines (no <p>), try to find in text
                if (preg_match('/@icon\s*:\s*([^\s<]+)/i', $partHtml, $m)) {
                    $icon = trim($m[1]);
                    $partHtml = preg_replace('/@icon\s*:\s*[^\r\n<]+/i', '', $partHtml);
                }
                if (preg_match('/@link\s*:\s*([^\s<]+)/i', $partHtml, $m)) {
                    $link = trim($m[1]);
                    $partHtml = preg_replace('/@link\s*:\s*[^\r\n<]+/i', '', $partHtml);
                }
                if (preg_match('/@cta\s*:\s*([^\r\n<]+)/i', $partHtml, $m)) {
                    $cta = trim($m[1]);
                    $partHtml = preg_replace('/@cta\s*:\s*[^\r\n<]+/i', '', $partHtml);
                }
            }

            // Trim leftover HTML
            $bodyHtml = trim($partHtml);

            // From the remaining body, use first paragraph/text as the title if exists
            $titleHtml = '';
            $bodyRestHtml = $bodyHtml;

            if (preg_match('/<p\b[^>]*>(.*?)<\/p>/is', $bodyHtml, $pm)) {
                // first paragraph becomes title
                $titleText = trim(strip_tags($pm[1]));
                $titleHtml = $this->escapeHtml($titleText);
                // remove that first paragraph from body
                $bodyRestHtml = preg_replace('/<p\b[^>]*>.*?<\/p>/is', '', $bodyHtml, 1);
            } else {
                // no <p>, maybe raw text - use as title
                $plain = trim(strip_tags($bodyHtml));
                if ($plain !== '') {
                    $titleHtml = $this->escapeHtml($plain);
                    $bodyRestHtml = '';
                }
            }

            // Build icon element (if present) — note: we keep <img> markup, not escaped
            $iconHtml = '';
            if ($icon !== null && $icon !== '') {
                // normalize: allow either "academic-cap" or "academic-cap.svg"
                $name = preg_replace('/\.svg$/i', '', $icon);
                $src = '/docs/heroicons/main/usage/' . $this->escapeUrl($name . '.svg');
                $iconImg = new HtmlElement('img', [
                    'src' => $src,
                    'alt' => $this->escapeHtml($name),
                    'class' => 'w-4 h-4',
                ], '');
                $iconHtml = (string) $iconImg;
            }

            // Build CTA anchor if link present
            $ctaHtml = '';
            if ($link !== null && $link !== '') {
                $ctaText = $cta !== null ? $this->escapeHtml($cta) : 'Read';
                $aInner = $this->escapeHtml($ctaText)
                        . new HtmlElement('svg', [
                            'class' => 'w-3 h-3 ms-2 rtl:rotate-180',
                            'xmlns' => 'http://www.w3.org/2000/svg',
                            'fill' => 'none',
                            'viewBox' => '0 0 14 10',
                            'aria-hidden' => 'true',
                        ], '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>');

                $ctaHtml = (string) new HtmlElement('a', [
                    'href' => $this->escapeUrl($link),
                    'class' => 'inline-flex items-center px-4 py-2 mt-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100'
                ], $aInner);
            }

            // Compose the list item markup similar to your example
            $iconWrapper = new HtmlElement('span', [
                'class' => 'absolute -start-3 flex items-center justify-center w-6 h-6 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full shadow-sm'
            ], $iconHtml ?: '');

            $timeHtml = $headingText ? new HtmlElement('time', ['class' => 'mb-1 text-sm text-gray-500 dark:text-gray-400'], $this->escapeHtml($headingText)) : '';

            $titleElement = $titleHtml !== '' ? new HtmlElement('h3', ['class' => 'text-lg font-semibold text-gray-900 dark:text-white'], $titleHtml) : '';

            $bodyElement = $bodyRestHtml !== '' ? new HtmlElement('div', ['class' => 'mt-2 text-sm text-gray-700 dark:text-gray-300'], $bodyRestHtml) : '';

            $liInner = $iconWrapper
                     . $timeHtml
                     . $titleElement
                     . $bodyElement
                     . $ctaHtml;

            $stepsHtml .= new HtmlElement('li', ['class' => 'mb-10 ms-6'], $liInner);
        }

        // Wrap all steps in a ul with the classes you gave
        $ul = new HtmlElement('ul', ['class' => 'relative border-s border-gray-200 dark:border-gray-700 ms-6'], $stepsHtml);

        // Optionally place a wrapper div with data-type from info string
        $typeAttr = $this->escapeHtml((string) $node->getType());
        $container = new HtmlElement('div', ['class' => 'zaimea-timeline', 'data-type' => $typeAttr], $ul);

        return $container;
    }

    private function escapeHtml(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function escapeUrl(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
