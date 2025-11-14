<?php

namespace Zaimea\CommonMark\Timeline\Nodes;

use League\CommonMark\Node\Block\AbstractBlock;

class TimelineItemBlock extends AbstractBlock
{
    public string $time = '';
    public string $icon = '';
    public string $link = '';
    public string $cta = '';
    public string $content = '';

    public function setParent(TimelineBlock $parent): void
    {
        $this->setParent($parent);
    }
}
