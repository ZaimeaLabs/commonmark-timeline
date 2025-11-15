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
use Zaimea\CommonMark\Timeline\Event\TimelineBlockConverter;
use League\CommonMark\Event\DocumentParsedEvent;

final class TimelineExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // Keep block start parsers for the "normal" case (if the fence is on its own line)
        $environment->addBlockStartParser(new TimelineStartParser());
        $environment->addBlockStartParser(new TimelineItemStartParser());

        // Container parser, renderers
        $environment->addRenderer(TimelineBlock::class, new TimelineBlockRenderer());
        $environment->addRenderer(TimelineItemBlock::class, new TimelineItemRenderer());

        // Event-based converter that repairs AST if ::: timeline was parsed into paragraphs
        // Run this *after* parsing (high priority so it runs early in DocumentParsedEvent handling)
        $environment->addEventListener(DocumentParsedEvent::class, new TimelineBlockConverter(), 1000);
    }
}
