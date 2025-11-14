<?php

namespace Zaimea\CommonMark\Timeline;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Block\BlockContinueParserWithInlinesInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\InlineParserEngineInterface;
use League\CommonMark\Util\ArrayCollection;
use League\CommonMark\Util\RegexHelper;

class TimelineParser extends AbstractBlockContinueParser implements BlockContinueParserWithInlinesInterface
{
    /** @psalm-readonly */
    private Timeline $block;

    /** @var ArrayCollection<string> */
    private ArrayCollection $strings;

    public function __construct()
    {
        $this->block = new Timeline();
        $this->strings = new ArrayCollection();
    }

    public function getBlock(): Timeline
    {
        return $this->block;
    }

    public function isContainer(): bool
    {
        return false;
    }

    public function canContain(AbstractBlock $childBlock): bool
    {
        return false;
    }

    public function canHaveLazyContinuationLines(): bool
    {
        return true;
    }

    public function parseInlines(InlineParserEngineInterface $inlineParser): void
    {
        // Let the engine parse the literal content for inline nodes
        $inlineParser->parse($this->block->getLiteral(), $this->block);
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        // closing fence exactly ":::" on its own (allow trailing spaces)
        $pos = $cursor->getRemainder();
        if (preg_match('/^:::\s*$/', $pos)) {
            return BlockContinue::finished();
        }

        // otherwise continue reading lines (we accept indented content as part of step body)
        $cursor->advanceToNextNonSpaceOrTab();
        $cursor->advanceBySpaceOrTab();

        return BlockContinue::at($cursor);
    }

    public function addLine(string $line): void
    {
        // store raw lines as they come
        $this->strings[] = $line;
    }

    public function closeBlock(): void
    {
        // first line becomes header (info string)
        $firstLine = $this->strings->first();
        if ($firstLine === false) {
            $firstLine = '';
        }

        $this->block->setHeader(RegexHelper::unescape(trim((string) $firstLine)));

        if ($this->strings->count() === 1) {
            $this->block->setLiteral('');
        } else {
            // join remaining lines as literal (keep final newline to match CommonMark blocks behaviour)
            $this->block->setLiteral(implode("\n", $this->strings->slice(1)) . "\n");
        }
    }
}
