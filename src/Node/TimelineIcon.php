<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Node;

use League\CommonMark\Node\Block\AbstractBlock;

final class TimelineIcon extends AbstractBlock
{
    public string $name = '';

    public function __construct(string $name = '')
    {
        parent::__construct();
        $this->name = $name;
    }
}
