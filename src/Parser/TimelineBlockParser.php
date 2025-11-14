<?php

namespace Zaimea\CommonMark\Timeline\Parser;

use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use Zaimea\CommonMark\Timeline\Nodes\TimelineBlock;
use Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;

class TimelineBlockParser extends AbstractBlockContinueParser implements BlockContinueParserInterface
{
    private TimelineBlock $block;
    private array $lines = [];

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

        // closing fence
        if (preg_match('/^:::\s*$/', $rest)) {
            return BlockContinue::finished();
        }

        return BlockContinue::at($cursor);
    }

    public function addLine(string $line): void
    {
        $this->lines[] = $line;
    }

    public function closeBlock(): void
    {
        $currentItem = null;

        foreach ($this->lines as $line) {
            $line = trim($line);

            // detect new item (### Time)
            if (preg_match('/^###\s+(.*)$/', $line, $matches)) {
                $currentItem = new TimelineItemBlock();
                $currentItem->time = $matches[1];
                $this->block->addItem($currentItem);
                continue;
            }

            // detect @ directives
            if ($currentItem && preg_match('/^@icon:\s*(.+)$/', $line, $matches)) {
                $currentItem->icon = $matches[1];
                continue;
            }

            if ($currentItem && preg_match('/^@link:\s*(.+)$/', $line, $matches)) {
                $currentItem->link = $matches[1];
                continue;
            }

            if ($currentItem && preg_match('/^@cta:\s*(.+)$/', $line, $matches)) {
                $currentItem->cta = $matches[1];
                continue;
            }

            // append normal content
            if ($currentItem) {
                $currentItem->content .= ($currentItem->content !== '' ? "\n" : '') . $line;
            }
        }
    }
}
