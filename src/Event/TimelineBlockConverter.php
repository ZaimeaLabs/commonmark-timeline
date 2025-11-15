<?php
declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Event;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\NodeIterator;
use Zaimea\CommonMark\Timeline\Nodes\TimelineBlock;
use Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;

final class TimelineBlockConverter
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        $document = $event->getDocument();

        // iterate blocks only
        foreach ($document->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            // we only care about paragraphs that start with "::: timeline"
            if (! $node instanceof Paragraph) {
                continue;
            }

            $text = $this->getBlockText($node);
            if ($text === null) {
                continue;
            }

            if (! preg_match('/^:::\s*timeline\b/i', trim($text))) {
                continue;
            }

            // found starting paragraph for a timeline
            $timeline = new TimelineBlock();

            // we'll collect sibling nodes until closing marker (:::) is found
            $next = $node->next();
            $currentItem = null;
            $endNode = null;

            while ($next !== null) {
                // check for closing marker paragraph ":::"
                if ($next instanceof Paragraph) {
                    $nextText = (string) $this->getBlockText($next);
                    if (preg_match('/^:::\s*$/', trim($nextText))) {
                        $endNode = $next;
                        break;
                    }
                }

                // If a level-3 heading => start new item
                if ($next instanceof Heading && $next->getLevel() === 3) {
                    $currentItem = new TimelineItemBlock();
                    $currentItem->time = (string) $this->getBlockText($next);
                    $timeline->appendChild($currentItem);

                    // detach the heading (we will move content references)
                    $tmp = $next->next();
                    $next->detach();
                    $next = $tmp;
                    continue;
                }

                // If we have a current item, move content under it
                if ($currentItem !== null) {
                    // handle paragraphs specially for @meta directives
                    if ($next instanceof Paragraph) {
                        $plain = trim((string) $this->getBlockText($next));
                        $lines = preg_split('/\R/', $plain) ?: [];

                        $handled = false;
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if ($line === '') {
                                continue;
                            }
                            if (preg_match('/^@icon:\s*(.+)$/i', $line, $m)) {
                                $currentItem->icon = trim($m[1]);
                                $handled = true;
                                continue;
                            }
                            if (preg_match('/^@link:\s*(.+)$/i', $line, $m)) {
                                $currentItem->link = trim($m[1]);
                                $handled = true;
                                continue;
                            }
                            if (preg_match('/^@cta:\s*(.+)$/i', $line, $m)) {
                                $currentItem->cta = trim($m[1]);
                                $handled = true;
                                continue;
                            }
                        }

                        if ($handled) {
                            // remove this paragraph (it contained only meta lines)
                            $tmp = $next->next();
                            $next->detach();
                            $next = $tmp;
                            continue;
                        }

                        // otherwise move paragraph node into current item (preserve its children)
                        $tmp = $next->next();
                        $next->detach();
                        $currentItem->appendChild($next);
                        $next = $tmp;
                        continue;
                    }

                    // other block types (list, code, etc.) — move under current item
                    $tmp = $next->next();
                    $next->detach();
                    $currentItem->appendChild($next);
                    $next = $tmp;
                    continue;
                }

                // If we haven't encountered a heading yet -> skip/move on
                $next = $next->next();
            } // end while siblings

            // replace the original opening paragraph with the timeline block
            $node->replaceWith($timeline);

            // if we found an explicit end marker, detach it
            if ($endNode !== null) {
                $endNode->detach();
            }
        }
    }

    /**
     * Get a plain text representation for a block node (Paragraph/Heading).
     * Returns null if no children.
     */
    private function getBlockText(Node $block): ?string
    {
        $out = '';
        $child = $block->firstChild();
        while ($child !== null) {
            if ($child instanceof Text) {
                $out .= $child->getLiteral();
            } else {
                // For other inlines, try to access literal if present (code, emph etc.)
                if (method_exists($child, 'getLiteral')) {
                    $out .= $child->getLiteral();
                }
            }

            $child = $child->next();
        }

        return $out === '' ? null : $out;
    }
}
