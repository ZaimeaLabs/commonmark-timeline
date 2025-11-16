<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Node;

use League\CommonMark\Node\Block\AbstractBlock;

final class TimelineTime extends AbstractBlock
{
    public string $text = '';

    public function __construct(string $text = '')
    {
        parent::__construct();
        $this->text = $text;
    }
}
