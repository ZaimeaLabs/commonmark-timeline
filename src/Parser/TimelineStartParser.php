<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Parser;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

/**
 * Recognizes the opening fence: ::: timeline
 */
final class TimelineStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();

        $fence = $cursor->match('/^:::\s*timeline(?:\s*<br\s*\/?>)?/i');
        if ($fence === null) {
            return BlockStart::none();
        }

        // start the container parser for the timeline
        return BlockStart::of(new TimelineBlockContinueParser())->at($cursor);
    }
}
