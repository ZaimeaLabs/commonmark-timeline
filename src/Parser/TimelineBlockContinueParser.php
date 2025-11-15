<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Parser;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use Zaimea\CommonMark\Timeline\Nodes\TimelineBlock;

/**
 * Container parser for the timeline block. Accepts TimelineItemBlock children.
 */
final class TimelineBlockContinueParser extends AbstractBlockContinueParser
{
    private TimelineBlock $block;

    public function __construct()
    {
        $this->block = new TimelineBlock();
    }

    public function getBlock(): TimelineBlock
    {
        return $this->block;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        $rest = $cursor->getRemainder();

        // closing fence ends the timeline
        if (preg_match('/^:::\s*$/', $rest)) {
            return BlockContinue::finished();
        }

        // otherwise allow children (TimelineItemStartParser will start item blocks)
        return BlockContinue::at($cursor);
    }

    public function isContainer(): bool
    {
        return true;
    }

    public function canContain(AbstractBlock $childBlock): bool
    {
        return $childBlock instanceof \Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;
    }
}
