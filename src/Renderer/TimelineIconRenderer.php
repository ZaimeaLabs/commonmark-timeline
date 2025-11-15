<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\BladeRender\BladeRender;
use Zaimea\CommonMark\Timeline\Nodes\TimelineBlock;

final class TimelineIconRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineBlock $node
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        BladeRender::assertInstanceOf($node);

        // render the BladeRender node — BladeRenderRenderer will call Blade::render()
        $iconHtml = $childRenderer->renderNodes([$node]);

        return new HtmlElement('span', [
                'class' => 'absolute -start-3 flex items-center justify-center w-6 h-6 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full shadow-sm'
            ], $iconHtml);
    }
}
