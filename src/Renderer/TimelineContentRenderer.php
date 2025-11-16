<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Zaimea\CommonMark\Timeline\Node\TimelineContent;
use League\CommonMark\Util\HtmlElement;

final class TimelineContentRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineContent $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        TimelineContent::assertInstanceOf($node);
        // Render inner children as-is (paragraphs, inline, lists)
        return new HtmlElement('div', ['class' => ''], $childRenderer->renderNodes($node->children()));
    }
}
