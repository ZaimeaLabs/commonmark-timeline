<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Event;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\NodeIterator;
use Zaimea\CommonMark\Timeline\Node\Timeline;
use Zaimea\CommonMark\Timeline\Node\TimelineList;
use Zaimea\CommonMark\Timeline\Node\TimelineOrderedLists;
use Zaimea\CommonMark\Timeline\Node\TimelineIcon;
use Zaimea\CommonMark\Timeline\Node\TimelineTime;
use Zaimea\CommonMark\Timeline\Node\TimelineContent;
use Zaimea\CommonMark\Timeline\Node\TimelineCta;

/**
 * LooseTimelineHandler
 *
 * Converts loose timeline-like paragraphs into structured TimelineOrderedLists -> TimelineList
 * and extracts @icon, @link, @cta metadata into dedicated nodes.
 *
 * Supports two input patterns:
 *  - Description style with ":timeline   ..." lines (typical in your examples)
 *  - Sections with ### headings inside the timeline block
 */
final class LooseTimelineHandler
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        $document = $event->getDocument();

        foreach ($document->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $timeline) {
            if (! $timeline instanceof Timeline) {
                continue;
            }

            // Ensure wrapper list exists (TimelineOrderedLists)
            if (! $timeline->parent() instanceof TimelineOrderedLists) {
                $list = new TimelineOrderedLists();
                if (($paragraph = $timeline->previous()) instanceof Paragraph) {
                    // take preceding paragraph (term/label) with it
                    $list->appendChild($paragraph);
                }

                $timeline->replaceWith($list);
                $list->appendChild($timeline);
            }

            // Now the paragraph we want to convert is the one just before the timeline node
            $paragraph = $timeline->previous();
            if (! $paragraph instanceof Paragraph) {
                // nothing to convert
                continue;
            }

            // Convert paragraph which contains "terms" and possibly :timeline lines into list items
            $items = $this->parseParagraphIntoItems($paragraph);

            // Replace the paragraph with first TimelineList and append others after
            if (count($items) === 0) {
                // fallback: replace paragraph with empty TimelineList so structure remains
                $empty = new TimelineList();
                $paragraph->replaceWith($empty);
                continue;
            }

            // Replace the paragraph node by the first item, then insert subsequent ones
            $firstItem = array_shift($items);
            $paragraph->replaceWith($firstItem);

            $last = $firstItem;
            foreach ($items as $it) {
                $last->insertAfter($it);
                $last = $it;
            }

            // Additionally: if timeline block contains Heading level 3 children, convert them
            // into TimelineList nodes too and move following blocks under them.
            $this->absorbHeadingsInsideTimeline($timeline);
        }
    }

    /**
     * Parse a Paragraph block that contains terms + :timeline lines into an array of TimelineList nodes.
     *
     * The paragraph text is split into lines (respecting Newline inline nodes).
     * Lines that are plain (not starting with :timeline) are treated as "term/title" (time).
     * Lines starting with ":timeline" are treated as content for the current term.
     *
     * Also extracts metadata lines starting with @icon:, @link:, @cta: when present inside content
     * and converts them to dedicated nodes on the TimelineList.
     *
     * @return TimelineList[]
     */
    private function parseParagraphIntoItems(Paragraph $paragraph): array
    {
        // Build the plain lines array from paragraph inline children (preserve Newline)
        $lines = $this->linesFromBlock($paragraph);

        $items = [];
        $currentTitle = null; // the last seen non-:timeline line
        $currentContents = []; // array of lines for the current item
        foreach ($lines as $rawLine) {
            $line = rtrim($rawLine, "\r\n");

            // If line begins with ":timeline" -> content line for currentTitle
            if (preg_match('/^\s*:timeline\b\s*(.*)$/i', $line, $m)) {
                $contentPart = isset($m[1]) ? $m[1] : '';
                $currentContents[] = $contentPart;
                continue;
            }

            // Empty line — skip (or treat as paragraph break)
            if (trim($line) === '') {
                // treat as newline inside content if we already have an item
                if ($currentTitle !== null) {
                    $currentContents[] = '';
                }
                continue;
            }

            // Non :timeline line — assume it's a new title/term
            // If we already had a currentTitle with content, finalize previous item
            if ($currentTitle !== null && count($currentContents) > 0) {
                $items[] = $this->buildTimelineListNode($currentTitle, $currentContents);
                $currentContents = [];
            }

            // Set new title (term)
            $currentTitle = $line;
        }

        // flush last item if any
        if ($currentTitle !== null && (count($currentContents) > 0 || true)) {
            // if no content lines but we have a title, we still create an item (empty content)
            $items[] = $this->buildTimelineListNode($currentTitle, $currentContents);
        }

        return $items;
    }

    /**
     * Build a TimelineList node from a title and array of content lines.
     * Extracts metadata lines (@icon, @link, @cta) from the content lines.
     */
    private function buildTimelineListNode(string $title, array $contentLines): TimelineList
    {
        $item = new TimelineList();

        // Create and append TimelineTime (title)
        $timeNode = new TimelineTime($title);
        $item->appendChild($timeNode);

        // Extract metadata from content lines (@icon, @link, @cta)
        $iconName = null;
        $link = null;
        $ctaText = null;
        $filteredContentLines = [];

        foreach ($contentLines as $line) {
            $trim = trim($line);
            if ($trim === '') {
                $filteredContentLines[] = '';
                continue;
            }

            if (preg_match('/^@icon:\s*(.+)$/i', $trim, $m)) {
                $iconName = trim($m[1]);
                continue;
            }
            if (preg_match('/^@link:\s*(.+)$/i', $trim, $m)) {
                $link = trim($m[1]);
                continue;
            }
            if (preg_match('/^@cta:\s*(.+)$/i', $trim, $m)) {
                $ctaText = trim($m[1]);
                continue;
            }

            $filteredContentLines[] = $line;
        }

        // If icon meta found, append TimelineIcon node
        if ($iconName !== null && $iconName !== '') {
            $iconNode = new TimelineIcon($iconName);
            $item->appendChild($iconNode);
        }

        // Create TimelineContent and append a Paragraph block with combined content lines
        $contentNode = new TimelineContent();
        if (count($filteredContentLines) > 0) {
            // build a single Paragraph block containing the joined content (preserve line breaks)
            $paragraph = new Paragraph();
            $text = new Text(implode("\n", $filteredContentLines));
            $paragraph->appendChild($text);
            $contentNode->appendChild($paragraph);
        }
        $item->appendChild($contentNode);

        // CTA
        if ($link !== null && $link !== '') {
            $ctaNode = new TimelineCta($link, $ctaText ?? 'Read guide');
            $item->appendChild($ctaNode);
        }

        return $item;
    }

    /**
     * Extract array of lines from a block by concatenating inline Text nodes and inserting
     * newline markers when Newline inline nodes appear.
     *
     * @return string[] lines (without trailing newline)
     */
    private function linesFromBlock(Node $block): array
    {
        $out = '';
        $child = $block->firstChild();
        while ($child !== null) {
            if ($child instanceof Text) {
                $out .= $child->getLiteral();
            } elseif ($child instanceof Newline) {
                $out .= "\n";
            } else {
                // If inline has literal, append it
                if (method_exists($child, 'getLiteral')) {
                    $out .= (string) $child->getLiteral();
                }
            }
            $child = $child->next();
        }

        // Normalize CRLF and split
        $out = str_replace(["\r\n", "\r"], "\n", $out);
        $lines = explode("\n", $out);

        // Trim trailing newlines
        return $lines;
    }

    /**
     * If timeline block contains headings level 3 inside, convert them into TimelineList items
     * and move following content blocks under those items until the next heading or end.
     *
     * This handles the alternative format where items are delimited by ### headings inside the timeline.
     */
    private function absorbHeadingsInsideTimeline(Timeline $timeline): void
    {
        $child = $timeline->firstChild();
        $currentItem = null;
        while ($child !== null) {
            $next = $child->next();

            if ($child instanceof Heading && $child->getLevel() === 3) {
                // start a new TimelineList
                $item = new TimelineList();
                $timeText = $this->extractInlineText($child);
                $timeNode = new TimelineTime($timeText);
                $item->appendChild($timeNode);

                // place the item after the timeline (or as appropriate)
                $timeline->appendChild($item);

                // detach heading
                $child->detach();

                $currentItem = $item;
                $child = $next;
                continue;
            }

            if ($currentItem !== null) {
                // move this block under current item (paragraphs, lists, code blocks)
                $tmp = $next;
                $child->detach();
                $currentItem->appendChild($child);
                $child = $tmp;
                continue;
            }

            $child = $next;
        }
    }

    /**
     * Extract plain text from inline children of a block node (concatenate literals).
     */
    private function extractInlineText(Node $block): string
    {
        $out = '';
        $child = $block->firstChild();
        while ($child !== null) {
            if (method_exists($child, 'getLiteral')) {
                $lit = (string) $child->getLiteral();
                if ($lit !== '') {
                    $out .= $lit;
                }
            }
            $child = $child->next();
        }

        return trim($out);
    }
}
