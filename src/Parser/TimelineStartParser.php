<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Parser;

use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use Zaimea\CommonMark\Timeline\Node\Timeline;

final class TimelineStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();
        if ($cursor->match('/^:step[ \t]+/') === null) {
            return BlockStart::none();
        }

        $terms = $parserState->getParagraphContent();

        $activeBlock = $parserState->getActiveBlockParser()->getBlock();

        if ($terms !== null && $terms !== '') {
            // New timeline; tight; term(s) sitting in pending block that we will replace
            return BlockStart::of(...[new TimelineListContinueParser()], ...self::splitTerms($terms), ...[new TimelineContinueParser(true, $cursor->getPosition())])
                ->at($cursor)
                ->replaceActiveBlockParser();
        }

        if ($activeBlock instanceof Paragraph && $activeBlock->parent() instanceof Timeline) {
            // Additional timeline in the same list as the parent timeline
            return BlockStart::of(new TimelineContinueParser(true, $cursor->getPosition()))->at($cursor);
        }

        if ($activeBlock->lastChild() instanceof Paragraph) {
            // New timeline; loose; term(s) sitting in previous closed paragraph block
            return BlockStart::of(new TimelineContinueParser(false, $cursor->getPosition()))->at($cursor);
        }

        // No preceding terms
        return BlockStart::none();
    }

    /**
     * @return array<int, TimelineTermContinueParser>
     */
    private static function splitTerms(string $terms): array
    {
        $ret = [];
        foreach (\explode("\n", $terms) as $term) {
            $ret[] = new TimelineTermContinueParser($term);
        }

        return $ret;
    }
}
