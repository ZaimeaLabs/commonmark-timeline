<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Event;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\NodeIterator;
use Zaimea\CommonMark\Timeline\Node\Timeline;
use Zaimea\CommonMark\Timeline\Node\TimelineList;
use Zaimea\CommonMark\Timeline\Node\TimelineOrderedLists;

final class LooseTimelineHandler
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        foreach ($event->getDocument()->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $timeline) {
            if (! $timeline instanceof Timeline) {
                continue;
            }

            // Does this timeline need to be added to a ordered list?
            if (! $timeline->parent() instanceof TimelineOrderedLists) {
                $list = new TimelineOrderedLists();
                // Taking any preceding paragraphs with it
                if (($paragraph = $timeline->previous()) instanceof Paragraph) {
                    $list->appendChild($paragraph);
                }

                $timeline->replaceWith($list);
                $list->appendChild($timeline);
            }

            // Is this timeline preceded by a paragraph that should really be a list item?
            if (! (($paragraph = $timeline->previous()) instanceof Paragraph)) {
                continue;
            }

            // Convert the paragraph into one or more list items
            $list = new TimelineList();
            $paragraph->replaceWith($list);

            foreach ($paragraph->children() as $child) {
                if ($child instanceof Newline) {
                    $newList = new TimelineList();
                    $list->insertAfter($newList);
                    $list = $newList;
                    continue;
                }

                $list->appendChild($child);
            }
        }
    }
}
