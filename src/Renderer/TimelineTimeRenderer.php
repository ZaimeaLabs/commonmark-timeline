<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\Timeline\Node\TimelineTime;

final class TimelineTimeRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineTime $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        TimelineTime::assertInstanceOf($node);

        $text = trim($node->text);

        if ($text === '') {
            return new HtmlElement('time', ['class' => 'mb-1 text-sm text-gray-500 dark:text-gray-400'], '');
        }

        return new HtmlElement('time', ['class' => 'mb-1 text-sm text-gray-500 dark:text-gray-400'], htmlspecialchars($text));
    }
}
