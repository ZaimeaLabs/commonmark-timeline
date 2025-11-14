<?php

namespace Zaimea\CommonMark\Timeline\Parser;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

class TimelineStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        // Detect :::timeline
        $fence = $cursor->match('/^:::\s*timeline/i');
        if ($fence === null) {
            return BlockStart::none();
        }

        // Return a new parser for this block
        return BlockStart::of(new TimelineBlockParser())->at($cursor);
    }
}
