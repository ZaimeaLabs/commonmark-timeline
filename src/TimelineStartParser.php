<?php

namespace Zaimea\CommonMark\Timeline;

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

        // Allow forms: "::: timeline", ":::timeline", "::: timeline<BR/>" etc.
        $fence = $cursor->match('/^:::\s*timeline(?:\s*<br\s*\/?>)?/i');
        if ($fence === null) {
            return BlockStart::none();
        }

        return BlockStart::of(new TimelineParser())->at($cursor);
    }
}
