<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Event;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\NodeIterator;
use League\CommonMark\Node\Inline\Text;
use Zaimea\CommonMark\Timeline\Node\Timeline;
use Zaimea\CommonMark\Timeline\Node\TimelineIcon;
use Zaimea\CommonMark\Timeline\Node\TimelineCta;

final class TimelineAttributesListener
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        // iterate only block nodes
        foreach ($event->getDocument()->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if (! $node instanceof Timeline) {
                continue;
            }

            // check previous sibling
            $prev = $node->previous();
            if (! $prev instanceof Paragraph) {
                continue;
            }

            $paragraphText = $this->paragraphToString($prev);
            $paragraphTextTrim = ltrim($paragraphText);

            // pattern: :timeline optionally { ... } optionally followed by inline text
            // Capture group 1 = inside braces, group 2 = rest-of-line (text after attrs)
            if (preg_match('/^:timeline(?:[ \t]+)?(?:\{([^\}]*)\})?(?:[ \t]+(.*))?$/u', $paragraphTextTrim, $m) !== 1) {
                continue;
            }

            $attrString = isset($m[1]) ? trim($m[1]) : '';
            $inlineRest  = isset($m[2]) ? rtrim($m[2], " \t\r\n") : '';

            // if we have no attributes and no text after, it means just the :timeline token -> ignore
            if ($attrString === '' && $inlineRest === '') {
                // optional: if you want to remove the paragraph exactly if it is only :timeline, uncomment
                // $prev->detach();
                continue;
            }

            // parse attributes
            $attrs = $attrString !== '' ? $this->parseAttrs($attrString) : [];

            // Attach icon/cta to Timeline (only if they exist)
            if (! empty($attrs['icon'])) {
                $node->appendChild(new TimelineIcon($attrs['icon']));
            }

            if (! empty($attrs['link']) || ! empty($attrs['cta'])) {
                $href = $attrs['link'] ?? '';
                $text = $attrs['cta'] ?? '';
                $node->appendChild(new TimelineCta($href, $text));
            }

            // Clean the paragraph: remove the :timeline{...} prefix from it.
            // If there is text after the prefix (inlineRest), keep it; otherwise remove the entire paragraph.
            if ($inlineRest !== '') {
                // Replace the paragraph's textual content with the remaining text
                $this->replaceParagraphText($prev, $inlineRest);
            } else {
                // Remove the entire paragraph that contained only attributes
                $prev->detach();
            }
        }
    }

    /**
     * Concatenate the text content of a paragraph (Text nodes and Newline as space).
     */
    private function paragraphToString(Paragraph $p): string
    {
        $ret = '';
        foreach ($p->children() as $child) {
            if ($child instanceof Text) {
                $ret .= $child->literal;
            } else {
                // for non-text inline nodes (softbreak etc) we have a simple fallback
                $ret .= ' ';
            }
        }

        return trim($ret);
    }

    /**
     * Replace the paragraph's content with a single Text node containing $text.
     * Remove other child nodes.
     */
    private function replaceParagraphText(Paragraph $p, string $text): void
    {
        foreach (iterator_to_array($p->children()) as $child) {
            $child->detach();
        }

        $p->appendChild(new Text($text));
    }

    /**
     * Parse attribute string like: icon="name" link="/path" cta='Text' or key=value
     * returns associative array.
     */
    private function parseAttrs(string $attrString): array
    {
        $attrs = [];

        // Accept key="value", key='value' or key=value (no quotes).
        if (preg_match_all('/([a-zA-Z0-9_\-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s\}]+))/u', $attrString, $m, PREG_SET_ORDER)) {
            foreach ($m as $pair) {
                $key = $pair[1];
                $value = $pair[2] !== '' ? $pair[2] : ($pair[3] !== '' ? $pair[3] : $pair[4]);
                $attrs[$key] = $value;
            }
        }

        return $attrs;
    }
}
