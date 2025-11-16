<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\Timeline\Node\Timeline;

final class TimelineRenderer implements NodeRendererInterface
{
    /**
     * @param Timeline $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        Timeline::assertInstanceOf($node);

        return new HtmlElement('p', ['class' => 'mb-4 text-base font-normal text-body'], $childRenderer->renderNodes($node->children()));
    }
}
