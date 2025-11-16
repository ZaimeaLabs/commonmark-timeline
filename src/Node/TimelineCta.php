<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Node;

use League\CommonMark\Node\Block\AbstractBlock;

final class TimelineCta extends AbstractBlock
{
    public string $href = '';
    public string $text = '';

    public function __construct(string $href = '', string $text = '')
    {
        parent::__construct();
        $this->href = $href;
        $this->text = $text;
    }
}
