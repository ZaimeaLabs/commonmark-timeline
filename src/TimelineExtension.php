<?php

namespace Zaimea\CommonMark\Timeline;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use Zaimea\CommonMark\Timeline\Parser\TimelineStartParser;

class TimelineExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // Add parser for the opening fence
        $environment->addBlockStartParser(new TimelineStartParser());

        // Add renderer for TimelineBlock nodes
        $environment->addRenderer(Nodes\TimelineBlock::class, new TimelineRenderer());
        $environment->addRenderer(Nodes\TimelineItemBlock::class, new TimelineRenderer());
    }
}
