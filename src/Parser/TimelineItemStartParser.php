<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Parser;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use Zaimea\CommonMark\Timeline\Parser\TimelineItemContinueParser;
use Zaimea\CommonMark\Timeline\Nodes\TimelineBlock;

/**
 * Start parser for "### ..." lines inside a TimelineBlock.
 */
final class TimelineItemStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $active = $parserState->getActiveBlockParser()->getBlock();
        if (! $active instanceof TimelineBlock) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();

        $m = $cursor->match('/^###\s+(.+?)(?:\s*<br\s*\/?>)?/i');
        if ($m === null) {
            return BlockStart::none();
        }

        $time = trim($m);

        return BlockStart::of(new TimelineItemContinueParser($time))->at($cursor);
    }
}
