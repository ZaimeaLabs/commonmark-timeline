<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\BladeRender\BladeRender;
use Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;

final class TimelineCtaRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineItemBlock $node
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        TimelineItemBlock::assertInstanceOf($node);

        if ($node->link === '') {
            return '';
        }

        $ctaText = $node->cta !== '' ? htmlspecialchars($node->cta) : 'Read guide';

        $svgInner = '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>';
        $svg = (string) new HtmlElement('svg', [
            'class' => 'w-3 h-3 ms-2 rtl:rotate-180',
            'xmlns' => 'http://www.w3.org/2000/svg',
            'fill' => 'none',
            'viewBox' => '0 0 14 10',
            'aria-hidden' => 'true',
        ], $svgInner);

        return new HtmlElement('a', [
            'href' => htmlspecialchars($node->link),
            'class' => 'inline-flex items-center px-4 py-2 mt-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100'
        ], $ctaText . $svg);
    }
}
