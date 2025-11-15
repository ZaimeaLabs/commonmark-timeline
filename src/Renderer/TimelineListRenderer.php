<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\Timeline\Node\TimelineList;

final class TimelineListRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineList $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        TimelineList::assertInstanceOf($node);

        $separator = $childRenderer->getBlockSeparator();

        return new HtmlElement('dl', [], $separator . $childRenderer->renderNodes($node->children()) . $separator);
    }
}
