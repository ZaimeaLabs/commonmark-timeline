<?php
declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Event;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Node\NodeIterator;
use Zaimea\CommonMark\Timeline\Node\TimelineList;

final class ConsecutiveTimelineListMerger
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        foreach ($event->getDocument()->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if (! $node instanceof TimelineList) {
                continue;
            }

            if (! ($prev = $node->previous()) instanceof TimelineList) {
                continue;
            }

            foreach ($node->children() as $child) {
                $prev->appendChild($child);
            }

            $node->detach();
        }
    }
}
