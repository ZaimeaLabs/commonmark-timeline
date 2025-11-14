<?php

namespace Zaimea\CommonMark\Timeline;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

class TimelineExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // Add parser that recognizes the opening fence
        $environment->addBlockStartParser(new TimelineStartParser());

        // Add renderer for Timeline nodes
        $environment->addRenderer(Timeline::class, new TimelineRenderer());
    }
}
