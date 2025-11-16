<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\Timeline\Node\TimelineOrderedLists;

final class TimelineOrderedListsRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineOrderedLists $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        TimelineOrderedLists::assertInstanceOf($node);

        $separator = $childRenderer->getBlockSeparator();

        return new HtmlElement('ol', ['class' => 'relative border-s border-gray-200 dark:border-gray-700 ms-6'], $separator . $childRenderer->renderNodes($node->children()) . $separator);
    }
}
