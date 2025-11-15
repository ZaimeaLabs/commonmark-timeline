<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Parser;

use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Block\BlockContinueParserWithInlinesInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\InlineParserEngineInterface;
use League\CommonMark\Util\ArrayCollection;
use Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;
use Zaimea\CommonMark\BladeRender\BladeRender;

/**
 * Parses lines for one timeline item: collects lines, extracts @meta, then parses inline content.
 */
final class TimelineItemContinueParser extends AbstractBlockContinueParser implements BlockContinueParserWithInlinesInterface
{
    private TimelineItemBlock $block;

    /** @var ArrayCollection<string> */
    private ArrayCollection $strings;

    /** Literal content to be parsed by inline parser */
    private string $literal = '';

    public function __construct(string $time)
    {
        $this->block = new TimelineItemBlock();
        $this->block->time = $time;
        $this->strings = new ArrayCollection();
    }

    public function getBlock(): TimelineItemBlock
    {
        return $this->block;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        $rest = $cursor->getRemainder();

        // end on container close
        if (preg_match('/^:::\s*$/', $rest)) {
            return BlockContinue::finished();
        }

        // if next item heading appears, finish this item so the item start parser may take over
        if (preg_match('/^###\s+/', $rest)) {
            return BlockContinue::finished();
        }

        // else continue consuming lines
        return BlockContinue::at($cursor);
    }

    public function addLine(string $line): void
    {
        $this->strings[] = $line;
    }

    public function closeBlock(): void
    {
        // join and process lines: extract @icon/@link/@cta directives and build literal for inline parsing
        $all = implode("\n", $this->strings->toArray());
        $lines = preg_split('/\R/', $all) ?: [];

        $contentLines = [];
        foreach ($lines as $ln) {
            $trim = trim($ln);
            if ($trim === '') {
                $contentLines[] = '';
                continue;
            }

            if (preg_match('/^@icon:\s*(.+)$/i', $trim, $m)) {
                $this->block->icon = trim($m[1]);
                continue;
            }

            if (preg_match('/^@link:\s*(.+)$/i', $trim, $m)) {
                $this->block->link = trim($m[1]);
                continue;
            }

            if (preg_match('/^@cta:\s*(.+)$/i', $trim, $m)) {
                $this->block->cta = trim($m[1]);
                continue;
            }

            $contentLines[] = $ln;
        }

        $this->literal = trim(implode("\n", $contentLines));
    }

    /**
     * Parse accumulated literal content with the inline parser and attach children.
     * Also attach a BladeRender block child (first child) if an icon was specified.
     *
     * @param InlineParserEngineInterface $inlineParser
     */
    public function parseInlines(InlineParserEngineInterface $inlineParser): void
    {
        // if icon specified, attach a BladeRender block node as the first child
        if ($this->block->icon !== '') {
            $iconName = preg_replace('/\.svg$/i', '', $this->block->icon);
            $bladeContent = "<x-heroicons::outline." . $iconName . " class=\"w-4 h-4\" />";
            $bladeNode = new BladeRender($bladeContent);
            $this->block->appendChild($bladeNode);
        }

        if ($this->literal !== '') {
            $inlineParser->parse($this->literal, $this->block);
        }
    }
}
