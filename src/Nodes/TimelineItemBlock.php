<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Nodes;

use League\CommonMark\Node\Block\AbstractBlock;

/**
 * One timeline item. Inline content (paragraphs / inline nodes) are attached as children.
 */
final class TimelineItemBlock extends AbstractBlock
{
    public string $time = '';
    public string $icon = '';
    public string $link = '';
    public string $cta = '';
}
