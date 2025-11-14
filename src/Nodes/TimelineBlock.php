<?php

namespace Zaimea\CommonMark\Timeline\Nodes;

use League\CommonMark\Node\Block\AbstractBlock;
use Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;

class TimelineBlock extends AbstractBlock
{
    /** @var TimelineItemBlock[] */
    private array $items = [];

    public function addItem(TimelineItemBlock $item): void
    {
        $this->items[] = $item;
        $item->setParent($this);
    }

    /**
     * @return TimelineItemBlock[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
