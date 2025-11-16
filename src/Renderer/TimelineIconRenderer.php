<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Zaimea\CommonMark\Timeline\Node\TimelineIcon;

final class TimelineIconRenderer implements NodeRendererInterface
{
    /**
     * @param TimelineIcon $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        TimelineIcon::assertInstanceOf($node);

        $name = trim($node->name);

        if ($name === '') {
            return new HtmlElement('span', ['class' => 'absolute -start-3 flex items-center justify-center w-6 h-6'], '');
        }

        // Default behaviour: render <img> pointing to heroicons usage path.
        // If later you want Blade components, replace this rendering or register a different renderer.
        $img = new HtmlElement('img', [
            'src' => '/docs/heroicons/main/usage/' . htmlspecialchars($name) . '.svg',
            'alt' => htmlspecialchars($name),
            'class' => 'w-4 h-4'
        ]);

        return new HtmlElement('span', [
            'class' => 'absolute -start-3 flex items-center justify-center w-6 h-6 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full shadow-sm'
        ], (string) $img);
    }
}
