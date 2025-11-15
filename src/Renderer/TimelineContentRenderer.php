<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\BladeRender\BladeRender;
use Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;

final class TimelineContentRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineItemBlock $node
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        TimelineItemBlock::assertInstanceOf($node);

        // collect children
        $children = [];
        foreach ($node->children() as $c) {
            $children[] = $c;
        }

        if (!empty($children) && $children[0] instanceof BladeRender) {
            array_shift($children);
        }

        if (empty($contentChildren)) {
            return '';
        }

        $rendered = $childRenderer->renderNodes($contentChildren);

        // Wrap content in a block - you can change to <h3> if you want title styling
        return new HtmlElement('div', ['class' => 'mt-2'], $rendered);
    }
}
