<?php

namespace Zaimea\CommonMark\Timeline\Tests;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\MarkdownConverter;
use Zaimea\CommonMark\Timeline\TimelineExtension;

/**
 * Converts GitHub Flavored Markdown to HTML.
 */
class GithubFlavoredMarkdownConverter extends MarkdownConverter
{
    public $config = [];

    /**
     * Create a new Markdown converter pre-configured for GFM
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $environment = new Environment($this->config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new AttributesExtension());
        $environment->addExtension(new FrontMatterExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TimelineExtension());

        parent::__construct($environment);
    }

    public function getEnvironment(): EnvironmentInterface
    {
        \assert($this->environment instanceof EnvironmentInterface);

        return $this->environment;
    }
}
