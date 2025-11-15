<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\Timeline\Nodes\TimelineBlock;

final class TimelineBlockRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineBlock $node
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        TimelineBlock::assertInstanceOf($node);

        $separator = $childRenderer->getBlockSeparator();

        $inner = $childRenderer->renderNodes($node->children());

        return new HtmlElement('ul', ['class' => 'relative border-s border-gray-200 dark:border-gray-700 ms-6'], $separator . $inner . $separator);
    }
}
