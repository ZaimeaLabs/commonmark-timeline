<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use Zaimea\CommonMark\Timeline\Event\ConsecutiveTimelineOrderedListsMerger;
use Zaimea\CommonMark\Timeline\Event\LooseTimelineHandler;
use Zaimea\CommonMark\Timeline\Event\TimelineAttributesListener;
use Zaimea\CommonMark\Timeline\Node\Timeline;
use Zaimea\CommonMark\Timeline\Node\TimelineContent;
use Zaimea\CommonMark\Timeline\Node\TimelineCta;
use Zaimea\CommonMark\Timeline\Node\TimelineIcon;
use Zaimea\CommonMark\Timeline\Node\TimelineList;
use Zaimea\CommonMark\Timeline\Node\TimelineOrderedLists;
use Zaimea\CommonMark\Timeline\Node\TimelineTime;
use Zaimea\CommonMark\Timeline\Parser\TimelineStartParser;
use Zaimea\CommonMark\Timeline\Renderer\TimelineContentRenderer;
use Zaimea\CommonMark\Timeline\Renderer\TimelineCtaRenderer;
use Zaimea\CommonMark\Timeline\Renderer\TimelineIconRenderer;
use Zaimea\CommonMark\Timeline\Renderer\TimelineListRenderer;
use Zaimea\CommonMark\Timeline\Renderer\TimelineOrderedListsRenderer;
use Zaimea\CommonMark\Timeline\Renderer\TimelineRenderer;
use Zaimea\CommonMark\Timeline\Renderer\TimelineTimeRenderer;

final class TimelineExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new TimelineStartParser());

        $environment->addEventListener(DocumentParsedEvent::class, new LooseTimelineHandler(), 1001);
        $environment->addEventListener(DocumentParsedEvent::class, new ConsecutiveTimelineOrderedListsMerger(), 1000);

        $environment->addRenderer(TimelineOrderedLists::class, new TimelineOrderedListsRenderer());
        $environment->addRenderer(TimelineList::class, new TimelineListRenderer());
        $environment->addRenderer(TimelineIcon::class, new TimelineIconRenderer());
        $environment->addRenderer(TimelineTime::class, new TimelineTimeRenderer());
        $environment->addRenderer(TimelineContent::class, new TimelineContentRenderer());
        $environment->addRenderer(TimelineCta::class, new TimelineCtaRenderer());
        $environment->addRenderer(Timeline::class, new TimelineRenderer());

    }
}
