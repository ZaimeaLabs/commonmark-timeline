<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Parser;

use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Block\BlockContinueParserWithInlinesInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\InlineParserEngineInterface;
use Zaimea\CommonMark\Timeline\Node\TimelineTerm;

final class TimelineTermContinueParser extends AbstractBlockContinueParser implements BlockContinueParserWithInlinesInterface
{
    private TimelineTerm $block;

    private string $term;

    public function __construct(string $term)
    {
        $this->block = new TimelineTerm();
        $this->term  = $term;
    }

    public function getBlock(): TimelineTerm
    {
        return $this->block;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        return BlockContinue::finished();
    }

    public function parseInlines(InlineParserEngineInterface $inlineParser): void
    {
        if ($this->term !== '') {
            $inlineParser->parse($this->term, $this->block);
        }
    }
}
