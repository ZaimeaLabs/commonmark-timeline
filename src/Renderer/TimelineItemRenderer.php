<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;
use Zaimea\CommonMark\BladeRender\BladeRender;

final class TimelineItemRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineItemBlock $node
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        TimelineItemBlock::assertInstanceOf($node);

        // collect children
        $children = [];
        foreach ($node->children() as $c) {
            $children[] = $c;
        }

        $iconNode = null;
        if (!empty($children) && $children[0] instanceof BladeRender) {
            $iconNode = $children[0];
            array_shift($children);
        }

        $iconSpan = $this->renderIconSpan($iconNode, $childRenderer);
        $timeEl   = $this->renderTime($node);
        $contentEl = $this->renderContent($children, $childRenderer);
        $ctaEl     = $this->renderCta($node);

        $inner = $iconSpan . $timeEl . $contentEl . $ctaEl;

        return new HtmlElement('li', ['class' => 'mb-10 ms-6'], $inner);
    }

    private function renderIconSpan(?Node $iconNode, ChildNodeRendererInterface $childRenderer): string
    {
        $iconHtml = '';
        if ($iconNode !== null) {
            // render the BladeRender node — BladeRenderRenderer will call Blade::render()
            $iconHtml = $childRenderer->renderNodes([$iconNode]);
        }

        $span = new HtmlElement('span', [
            'class' => 'absolute -start-3 flex items-center justify-center w-6 h-6 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full shadow-sm'
        ], $iconHtml);

        return (string) $span;
    }

    private function renderTime(TimelineItemBlock $node): string
    {
        if ($node->time === '') {
            return '';
        }

        $timeEl = new HtmlElement('time', ['class' => 'mb-1 text-sm text-gray-500 dark:text-gray-400'], htmlspecialchars($node->time));
        return (string) $timeEl;
    }

    private function renderContent(array $contentChildren, ChildNodeRendererInterface $childRenderer): string
    {
        if (empty($contentChildren)) {
            return '';
        }

        $rendered = $childRenderer->renderNodes($contentChildren);

        // Wrap content in a block - you can change to <h3> if you want title styling
        return (string) new HtmlElement('div', ['class' => 'mt-2'], $rendered);
    }

    private function renderCta(TimelineItemBlock $node): string
    {
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

        $a = new HtmlElement('a', [
            'href' => htmlspecialchars($node->link),
            'class' => 'inline-flex items-center px-4 py-2 mt-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100'
        ], $ctaText . $svg);

        return (string) $a;
    }
}
