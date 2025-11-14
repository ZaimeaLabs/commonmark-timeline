<?php

namespace Zaimea\CommonMark\Timeline;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\Timeline\Nodes\TimelineBlock;
use Zaimea\CommonMark\Timeline\Nodes\TimelineItemBlock;

class TimelineRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineBlock|TimelineItemBlock $node
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        /** @var TimelineBlock|TimelineItemBlock $node */
        if ($node instanceof TimelineBlock) {
            $itemsHtml = '';
            foreach ($node->getItems() as $item) {
                $itemsHtml .= $childRenderer->renderNode($item);
            }

            return new HtmlElement('ul', ['class' => 'relative border-s border-gray-200 dark:border-gray-700 ms-6'], $itemsHtml);
        }

        if ($node instanceof TimelineItemBlock) {
            $iconHtml = '';
            if ($node->icon !== '') {
                $iconHtml = '<span class="absolute -start-3 flex items-center justify-center w-6 h-6 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full shadow-sm">';
                $iconHtml .= '<img src="/docs/heroicons/main/usage/' . htmlspecialchars($node->icon) . '.svg" alt="' . htmlspecialchars($node->icon) . '" class="w-4 h-4">';
                $iconHtml .= '</span>';
            }

            $timeHtml = $node->time !== '' ? '<time class="mb-1 text-sm text-gray-500 dark:text-gray-400">' . htmlspecialchars($node->time) . '</time>' : '';
            $contentHtml = '<h3 class="text-lg font-semibold text-gray-900 dark:text-white">' . nl2br(htmlspecialchars($node->content)) . '</h3>';

            $ctaHtml = '';
            if ($node->link !== '') {
                $ctaHtml = '<a href="' . htmlspecialchars($node->link) . '" class="inline-flex items-center px-4 py-2 mt-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100">';
                $ctaHtml .= htmlspecialchars($node->cta ?: 'Read more');
                $ctaHtml .= '<svg class="w-3 h-3 ms-2 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10" aria-hidden="true">';
                $ctaHtml .= '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/></svg>';
                $ctaHtml .= '</a>';
            }

            return new HtmlElement('li', ['class' => 'mb-10 ms-6'], $iconHtml . $timeHtml . $contentHtml . $ctaHtml);
        }

        return new HtmlElement('div', [], '');
    }
}
