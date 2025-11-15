<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;

final class TimelineTimeRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineItemBlock $node
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        TimelineItemBlock::assertInstanceOf($node);

        if ($node->time === '') {
            return '';
        }

        return new HtmlElement('time', ['class' => 'mb-1 text-sm text-gray-500 dark:text-gray-400'], htmlspecialchars($node->time));
    }
}
