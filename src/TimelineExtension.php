<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use Zaimea\CommonMark\Timeline\Parser\TimelineStartParser;
use Zaimea\CommonMark\Timeline\Parser\TimelineItemStartParser;
use Zaimea\CommonMark\Timeline\Parser\TimelineBlockContinueParser;
use Zaimea\CommonMark\Timeline\Renderer\TimelineBlockRenderer;
use Zaimea\CommonMark\Timeline\Renderer\TimelineItemRenderer;
use Zaimea\CommonMark\Timeline\Nodes\TimelineBlock;
use Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;

final class TimelineExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // start parser for the whole timeline block (::: timeline)
        $environment->addBlockStartParser(new TimelineStartParser());

        // start parser for timeline items inside a TimelineBlock (### ...)
        $environment->addBlockStartParser(new TimelineItemStartParser());

        // container parser is created by TimelineStartParser, but we register renderer for node types
        $environment->addRenderer(TimelineBlock::class, new TimelineBlockRenderer());
        $environment->addRenderer(TimelineItemBlock::class, new TimelineItemRenderer());
    }
}
