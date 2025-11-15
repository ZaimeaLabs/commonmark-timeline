<?php
declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use Zaimea\CommonMark\Timeline\Event\ConsecutiveTimelineListMerger;
use Zaimea\CommonMark\Timeline\Event\LooseTimelineHandler;
use Zaimea\CommonMark\Timeline\Node\Timeline;
use Zaimea\CommonMark\Timeline\Node\TimelineList;
use Zaimea\CommonMark\Timeline\Node\TimelineTerm;
use Zaimea\CommonMark\Timeline\Parser\TimelineStartParser;
use Zaimea\CommonMark\Timeline\Renderer\TimelineListRenderer;
use Zaimea\CommonMark\Timeline\Renderer\TimelineRenderer;
use Zaimea\CommonMark\Timeline\Renderer\TimelineTermRenderer;

final class TimelineExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new TimelineStartParser());

        $environment->addEventListener(DocumentParsedEvent::class, new LooseTimelineHandler(), 1001);
        $environment->addEventListener(DocumentParsedEvent::class, new ConsecutiveTimelineListMerger(), 1000);

        $environment->addRenderer(TimelineList::class, new TimelineListRenderer());
        $environment->addRenderer(TimelineTerm::class, new TimelineTermRenderer());
        $environment->addRenderer(Timeline::class, new TimelineRenderer());
    }
}
